<?php

namespace Takatost\PubSub\CMQ\Requests;

/**
 * Class DeleteMessageRequest
 * @package Takatost\PubSub\CMQ\Requests
 */
class DeleteMessageRequest extends BaseRequest
{
    /**
     * @var string
     */
    protected $action = 'DeleteMessage';

    /**
     * @var string
     */
    protected $type = self::TYPE_QUEUE;

    /**
     * @var string
     */
    protected $method = 'POST';

    protected $items = [
        'Action' => 'ReceiveMessage',

        /**
         * @var string 队列名
         */
        'queueName',

        /**
         * @var string receiptHandle
         */
        'receiptHandle',
    ];
}