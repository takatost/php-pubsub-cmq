<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase;
use Takatost\PubSub\CMQ\CMQPubSubAdapter;
use Takatost\PubSub\CMQ\HttpClient;

class CMQPubSubAdapterTest extends TestCase
{
    public function testGetClient()
    {
        $client = Mockery::mock(HttpClient::class);
        $adapter = new CMQPubSubAdapter($client);
        $this->assertSame($client, $adapter->getClient());
    }

    public function testSubscribe()
    {
        $loop = Mockery::mock('\Tests\Mocks\MockCMQPubSubLoop[subscribe]');
        $loop->shouldReceive('subscribe')
            ->with('queueName')
            ->once();
        $client = Mockery::mock(HttpClient::class);
        $client->shouldReceive('pubSubLoop')
            ->once()
            ->andReturn($loop);
        $adapter = new CMQPubSubAdapter($client);
        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldReceive('handle')
            ->with(['hello' => 'world'])
            ->once();
        $adapter->subscribe('queueName', [$handler1, 'handle']);
    }

    public function testPublish()
    {
        $client = Mockery::mock(HttpClient::class);
        $client->shouldReceive('publish')
            ->withArgs([
                'topicName',
                'a:1:{s:5:"hello";s:5:"world";}',
            ])
            ->once();
        $adapter = new CMQPubSubAdapter($client);
        $adapter->publish('topicName', ['hello' => 'world']);
    }
}
