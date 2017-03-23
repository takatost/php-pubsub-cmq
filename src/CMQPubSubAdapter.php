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
     * @var HttpClient
     */
    private $client;

    /**
     * CMQPubSubAdapter constructor.
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * @return HttpClient
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
                $message = $this->client->send($request);
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

            if ($message === null || $message->get('msgBody') === null) {
                sleep(mt_rand(1, 5));
                continue;
            }

            call_user_func($handler,
                Utils::unserializeMessagePayload($message->get('msgBody')),
                new MessageHandler($this->client, $topicQueueName, $message)
            );

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
        $params = [
            'topicName' => $topicName,
            'msgBody'   => Utils::serializeMessage($message),
        ];

        if ($tags != null  && is_array($tags) && !empty($tags))
        {
            $n = 1 ;
            foreach ($tags as $tag){
                $key = 'msgTag.' . $n;
                $params[$key]=$tag;
                $n += 1 ;
            }
        }

        $request = new PublishMessageRequest($params);

        try {
            $this->client->send($request);
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
