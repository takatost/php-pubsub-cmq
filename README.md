# php-pubsub-cmq
A Tencent CMQ adapter for the [php-pubsub](https://github.com/Superbalist/php-pubsub) package.

## Installation

```bash
composer require takatost/php-pubsub-cmq
```
## Usage

```php
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

$adapter = new \Takatost\PubSub\CMQ\CMQPubSubAdapter($config);

// consume messages
// note: this is a blocking call
$adapter->subscribe('topic_queue_name', function ($message) {
    var_dump($message);
});

// publish messages
$adapter->publish('topic_name', 'HELLO WORLD');
$adapter->publish('topic_name', json_encode(['hello' => 'world']));
$adapter->publish('topic_name', 1);
$adapter->publish('topic_name', false);
```
