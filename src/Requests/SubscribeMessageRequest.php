<?php

namespace Takatost\PubSub\CMQ\Requests;

/**
 * Class SubscribeMessageRequest
 * @package Takatost\PubSub\CMQ\Requests
 */
class SubscribeMessageRequest extends BaseRequest
{
    protected $method = 'POST';
    protected $items = [
        'Action' => 'ReceiveMessage',

        /**
         * @var string 队列名
         */
        'queueName',

        /**
         * @var string 本次请求的长轮询等待时间。取值范围 0-30 秒，默认值 0
         */
        'pollingWaitSeconds',
    ];
}