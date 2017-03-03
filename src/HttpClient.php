<?php

namespace Takatost\PubSub\CMQ;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Takatost\PubSub\CMQ\Exceptions\FailedResponseException;
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

    public function __construct(array $config)
    {
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
        $method = strtoupper($request->getMethod());
        $this->sign($request, $this->config);
        $attr = $request->all();

        $options[$method === 'POST' ? 'body' : 'query'] = http_build_query($attr);

        $promise = $this->client->requestAsync($method, $this->config['end_point'], $options);

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
        $request->put('SecretId', $this->config['secret_id']);
        $request->put('Signature', $this->makeSign($request, $config['secret_key'], $config['end_point']));

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

        $queryString = http_build_query($attr);

        $parseUrl = parse_url($endPoint);
        $srcString = $request->getMethod() . $parseUrl['host'] . $parseUrl['path'] . '?' . $queryString;

        return base64_encode(hash_hmac('sha1', $srcString, $secretKey, true));
    }
}