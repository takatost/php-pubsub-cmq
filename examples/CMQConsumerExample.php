<?php

include __DIR__ . '/../vendor/autoload.php';

$config = [
    'secret_key' => '',
    'secret_id'  => '',
    'queue_end_point'  => 'https://cmq-queue-sh.api.qcloud.com/v2/index.php',
    'topic_end_point'  => 'https://cmq-topic-sh.api.qcloud.com/v2/index.php',
    'options'    => [
        'debug'   => false,
        'timeout' => 10,
    ]
];

$client = new \Takatost\PubSub\CMQ\HttpClient($config);

$adapter = new \Takatost\PubSub\CMQ\CMQPubSubAdapter($client);

$adapter->subscribe('topic-queue-name', function ($message) {
    var_dump($message);
});