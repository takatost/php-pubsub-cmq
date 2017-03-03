<?php

include __DIR__ . '/../vendor/autoload.php';

$config = [
    'secret_key' => '',
    'secret_id'  => '',
    'end_point'  => 'https://cmq-queue-sh.api.qcloud.com/v2/index.php',
    'options'    => [
        'debug'   => false,
        'timeout' => 10,
    ]
];

$adapter = new \Takatost\PubSub\CMQ\CMQPubSubAdapter($config);

$adapter->subscribe('topic_queue_name', function ($message) {
    var_dump($message);
});