<?php

namespace Takatost\PubSub\CMQ;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Takatost\PubSub\CMQ\Exceptions\RequestException;
use Takatost\PubSub\CMQ\Exceptions\ResponseException;
use Takatost\PubSub\CMQ\Requests\BaseRequest;

/**
 * Class HttpClient
 * @package Takatost\PubSub\CMQ
 */
class HttpClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array GuzzleHttp options
     */
    private $options;

    /**
     * HttpClient constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (empty($config['options'])) {
            $this->options = [
                'debug'   => false,
                'timeout' => 10,
            ];
        } else {
            $this->options = $config['options'];
        }

        $this->config = $config;
        $this->client = new Client();
    }

    /**
     * @param BaseRequest $request
     * @param array       $options
     * @return mixed
     */
    public function send(BaseRequest $request, array $options = [])
    {
        $options = array_merge(
            $this->options,
            $options
        );

        $host = $this->config[$request->getType() . '_end_point'];
        $method = strtoupper($request->getMethod());
        $this->sign($request, $this->config);
        $attr = $request->all();

        $options[$method === 'POST' ? 'form_params' : 'query'] = $attr;

        $promise = $this->client->requestAsync($method, $host, $options);

        return $promise->then(function (ResponseInterface $response) {
            $body = $response->getBody()->getContents();
            $body = new Collection(json_decode($body, true));

            if (!$this->isSuccess($body)) {
                throw new ResponseException('response failed, code: ' . $body->get('code') . ', msg: ' . $body->get('message'), 0, $body);
            }

            return $body;
        }, function (\GuzzleHttp\Exception\RequestException $requestException) {
            throw new RequestException($requestException->getMessage(), 0, $requestException);
        })->wait();
    }

    /**
     * 返回是否成功
     * @param Collection $body
     * @return bool
     * @throws ResponseException
     * @author         JohnWang <takato@vip.qq.com>
     */
    protected function isSuccess(Collection $body)
    {
        if (! $body->has('code') || ! $body->has('message')) {
            throw new ResponseException('Response missing code or message', 0, $body);
        }

        if ($body->get('code') == 0) {
            return true;
        }

        return false;
    }

    /**
     * 整理并生成签名
     * @param BaseRequest $request
     * @param array           $config
     * @return BaseRequest
     * @author         JohnWang <takato@vip.qq.com>
     */
    protected function sign(BaseRequest $request, array $config)
    {
        $request->put('Nonce', mt_rand(10000, 9999999));
        $request->put('Timestamp', time());
        $request->put('SecretId', $config['secret_id']);
        $request->put('Signature', $this->makeSign($request, $config['secret_key'], $config[$request->getType() . '_end_point']));

        return $request;
    }

    /**
     * 生成签名
     * @param BaseRequest $request
     * @param string          $secretKey
     * @param string          $endPoint
     * @param string          $method
     * @return string
     */
    private function makeSign(BaseRequest $request, $secretKey, $endPoint)
    {
        $attr = $request->all();
        ksort($attr, SORT_STRING);

        $queryString = self::_buildParamStr($attr);

        $parseUrl = parse_url($endPoint);
        $srcString = $request->getMethod() . $parseUrl['host'] . $parseUrl['path'] . $queryString;

        return base64_encode(hash_hmac('sha1', $srcString, $secretKey, true));
    }

    /**
     * _buildParamStr
     * 拼接参数
     * @param  array $requestParams  请求参数
     * @param  string $requestMethod 请求方法
     * @return
     */
    protected static function _buildParamStr($requestParams, $requestMethod = 'POST')
    {
        $paramStr = '';
        ksort($requestParams);
        $i = 0;
        foreach ($requestParams as $key => $value)
        {
            if ($key === 'Signature')
            {
                continue;
            }

            // 排除上传文件的参数
            if ($requestMethod === 'POST' && substr($value, 0, 1) === '@') {
                continue;
            }

            // 把 参数中的 _ 替换成 .
            if (strpos($key, '_'))
            {
                $key = str_replace('_', '.', $key);
            }

            if ($i == 0)
            {
                $paramStr .= '?';
            }
            else
            {
                $paramStr .= '&';
            }

            $paramStr .= $key . '=' . $value;
            ++$i;
        }

        return $paramStr;
    }
}