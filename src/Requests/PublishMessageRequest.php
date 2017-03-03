<?php

namespace Takatost\PubSub\CMQ\Requests;

/**
 * Class PublishMessageRequest
 * @package Takatost\PubSub\CMQ\Requests
 */
class PublishMessageRequest extends BaseRequest
{
    protected $method = 'POST';
    protected $items = [
        'Action' => 'PublishMessage',

        /**
         * @var string 主题名字
         */
        'topicName',

        /**
         * @var string 消息正文。至少 1 Byte，最大长度受限于设置的主题消息最大长度属性
         */
        'msgBody',

        /**
         * @var array
         * 消息过滤标签。消息标签（用于消息过滤)。
         * 标签数量不能超过5个，每个标签不超过16个字符。
         * 与Subscribe接口的filterTag参数配合使用，规则：
         * 1）如果filterTag没有设置，则无论msgTag是否有设置，订阅接收所有发布到Topic的消息；
         * 2）如果filterTag数组有值，则只有数组中至少有一个值在msgTag数组中也存在时（即filterTag和msgTag有交集），订阅才接收该发布到Topic的消息；
         * 3）如果filterTag数组有值，但msgTag没设置，则不接收任何发布到Topic的消息，可以认为是2）的一种特例，此时filterTag和msgTag没有交集。
         * 规则整体的设计思想是以订阅者的意愿为主。
         */
        'msgTag'
    ];
}