<?php

include __DIR__ . '/../vendor/autoload.php';

$config = [
    'secret_id'  => '',
    'secret_key' => '',
    'queue_end_point'  => 'https://cmq-queue-sh.api.qcloud.com/v2/index.php',
    'topic_end_point'  => 'https://cmq-topic-sh.api.qcloud.com/v2/index.php',
    'options'    => [
        'debug'   => false,
        'timeout' => 5,
    ]
];

$client = new \Takatost\PubSub\CMQ\HttpClient($config);

$adapter = new \Takatost\PubSub\CMQ\CMQPubSubAdapter($client);

$adapter->publish('topic-name', 'HELLO WORLD');
$adapter->publish('topic-name', json_encode(['hello' => 'world']));
$adapter->publish('topic-name', 1);
$adapter->publish('topic-name', false);
