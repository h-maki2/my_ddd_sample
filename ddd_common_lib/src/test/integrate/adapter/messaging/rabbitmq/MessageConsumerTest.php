<?php

use dddCommonLib\adapter\messaging\rabbitmq\ConnectionSettings;
use dddCommonLib\adapter\messaging\rabbitmq\Exchange;
use dddCommonLib\adapter\messaging\rabbitmq\MessageConsumer;
use dddCommonLib\adapter\messaging\rabbitmq\MessageProducer;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqDeliveryMode;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqMessage;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqQueue;
use dddCommonLib\domain\model\eventStore\StoredEvent;
use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\test\helpers\event\TestEvent;
use PHPUnit\Framework\TestCase;

class MessageConsumerTest extends TestCase
{
    private RabbitMqQueue $queue;
    private string $testExchangeName;
    private string $testQueueName;
    private MessageProducer $producer;

    public function setUp(): void
    {
        $this->testExchangeName = 'test_exchange';
        $testConnection = new ConnectionSettings('rabbitmq', 'user', 'password', 5672);
        $this->testQueueName = 'test_queue';

        // エクスチェンジを作成する
        $exchange = Exchange::fanOutInstance(
            $testConnection,
            $this->testExchangeName,
            true
        );

        // プロデューサーを作成する
        $this->producer = new MessageProducer($exchange);

        // キューを作成する
        $this->queue = RabbitMqQueue::fromInstanceWithBindExchange(
            $exchange,
            $this->testQueueName
        );
    }

    public function tearDown(): void
    {
        $this->queue->channel->queue_purge($this->testQueueName);
        $this->queue->close();
    }

    public function test_コンシューマーでメッセージを受信できる()
    {
        // given
        // コンシューマを作成する
        $messageBody = null;
        $consumer = new MessageConsumer(
            $this->queue,
            $this->testExchangeName,
            [],
            function (Notification $notification) use (&$messageBody) {
                $messageBody = $notification;
            }
        );

        // 送信するメッセージを作成する
        $testEvent = new TestEvent();
        $storedEvent = StoredEvent::fromDomainEvent($testEvent);
        $notification = Notification::fromStoredEvent($storedEvent);
        $message = RabbitMqMessage::get($notification->serialize(), RabbitMqDeliveryMode::PERSISTENT);

        // when
        // メッセージを送信する
        $this->producer->send($message);

        // メッセージを受信する
        while ($consumer->channel()->is_consuming() && $messageBody === null) {
            $consumer->channel()->wait(null, false, 5); // 5秒でタイムアウト
        }

        // then
        $this->assertNotNull($messageBody);
    }
}