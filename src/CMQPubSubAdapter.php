<?php

namespace Takatost\PubSub\CMQ;

use Illuminate\Support\Collection;
use Superbalist\PubSub\PubSubAdapterInterface;
use Superbalist\PubSub\Utils;
use Takatost\PubSub\CMQ\Exceptions\RequestException;
use Takatost\PubSub\CMQ\Exceptions\ResponseException;
use Takatost\PubSub\CMQ\Requests\BaseRequest;
use Takatost\PubSub\CMQ\Requests\PublishMessageRequest;
use Takatost\PubSub\CMQ\Requests\SubscribeMessageRequest;

/**
 * Class CMQPubSubAdapter
 * @package Takatost\PubSub\CMQ
 */
class CMQPubSubAdapter implements PubSubAdapterInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var array GuzzleHttp options
     */
    private $options;

    /**
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
        $this->client = new HttpClient($config);
    }

    /**
     * Return the Config.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return HttpClient
     * @author         JohnWang <takato@vip.qq.com>
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Subscribe a handler to a topic queue.
     *
     * @param string   $topicQueueName
     * @param callable $handler
     * @throws RequestException
     * @throws ResponseException
     * @throws \Exception
     */
    public function subscribe($topicQueueName, callable $handler)
    {
        $isSubscriptionLoopActive = true;
        while ($isSubscriptionLoopActive) {
            $request = new SubscribeMessageRequest([
                'queueName'          => $topicQueueName,
                'pollingWaitSeconds' => 0,
            ]);

            try {
                $message = $this->client->send($request, $this->options);
            } catch (RequestException $e) {
                throw $e;
            } catch (ResponseException $e) {
                $message = $e->getBody();

                if (!$message->has('code')) {
                    throw $e;
                } else if (!in_array($message->get('code'), ['7000', '6070'])) {    // 队列没有消息或队列中有太多不可见或者延时消息
                    throw $e;
                }
            } catch (\Exception $e) {
                throw $e;
            }

            if ($message === null) {
                continue;
            }

            call_user_func($handler, Utils::unserializeMessagePayload($message->get('msgBody')));

            unset($message);
        }
    }

    /**
     * Publish a message to a topic.
     * @param string $topicName
     * @param mixed  $message
     * @param array  $tags
     * @throws RequestException
     * @throws ResponseException
     * @throws \Exception
     */
    public function publish($topicName, $message, $tags = [])
    {
        $request = new PublishMessageRequest([
            'topicName'          => $topicName,
            'msgBody' => Utils::serializeMessage($message),
            'msgTag' => $tags
        ]);

        try {
            $this->client->send($request, $this->options);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Send a request
     * @param BaseRequest $request
     * @param array       $options
     * @return Collection
     */
    public function send(BaseRequest $request, array $options = [])
    {
        return $this->client->send($request, $options);
    }
}
