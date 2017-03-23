<?php

namespace Takatost\PubSub\CMQ;


use Illuminate\Support\Collection;
use Takatost\PubSub\CMQ\Exceptions\RequestException;
use Takatost\PubSub\CMQ\Exceptions\ResponseException;
use Takatost\PubSub\CMQ\Requests\DeleteMessageRequest;

/**
 * 消息句柄
 * Class MessageHandler
 * @package Takatost\PubSub\CMQ
 */
class MessageHandler
{
    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $queueName;

    /**
     * @var Collection
     */
    protected $message;

    /**
     * MessageHandler constructor.
     */
    public function __construct($client, $queueName, $message)
    {
        $this->client = $client;
        $this->queueName = $queueName;
        $this->message = $message;
    }

    /**
     * Get QueueName
     * @return string
     */
    public function getQueueName()
    {
        return $this->queueName;
    }

    /**
     * Get Message
     * @return Collection
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * 删除消息
     * @throws \Exception
     */
    public function delete()
    {
        if (!$this->queueName) {
            throw new \Exception('QueueName 队列名称不存在');
        }

        if (!$this->message) {
            throw new \Exception('Message 消息不存在');
        }

        $params = [
            'queueName'     => $this->queueName,
            'receiptHandle' => $this->message->get('receiptHandle'),
        ];

        $request = new DeleteMessageRequest($params);

        try {
            $this->client->send($request);
        } catch (RequestException $e) {
            throw $e;
        } catch (ResponseException $e) {
            $message = $e->getBody();

            if (!$message->has('code')) {
                throw $e;
            } else if (!in_array($message->get('code'), ['4430', '4440'])) {    // 4430：句柄无效，4440：队列不存在
                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

}