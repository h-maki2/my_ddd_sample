<?php

use dddCommonLib\infrastructure\messaging\rabbitmq\ConnectionSettings;
use dddCommonLib\infrastructure\messaging\rabbitmq\Exchange;
use dddCommonLib\infrastructure\messaging\rabbitmq\MessageConsumer;
use dddCommonLib\infrastructure\messaging\rabbitmq\MessageProducer;
use dddCommonLib\infrastructure\messaging\rabbitmq\RabbitMqDeliveryMode;
use dddCommonLib\infrastructure\messaging\rabbitmq\RabbitMqMessage;
use dddCommonLib\infrastructure\messaging\rabbitmq\RabbitMqQueue;
use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\eventStore\StoredEvent;
use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\test\helpers\adapter\messaging\rabbitmq\TestRabbitMqMessageFactory;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use PHPUnit\Framework\TestCase;

class MessageConsumerTest extends TestCase
{
    private RabbitMqQueue $queue;
    private string $testExchangeName;
    private string $testQueueName;
    private MessageProducer $producer;
    private ?Notification $catchedNotification;

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
        $this->queue->delete();
        $this->queue->close();
    }

    public function test_コンシューマーでメッセージを受信できる()
    {
        // given
        $this->catchedNotification = null;
        // コンシューマを作成する
        $consumer = new MessageConsumer(
            $this->queue,
            $this->testExchangeName,
            [],
            function (string $messageBody) {
                $notification = JsonSerializer::deserialize($messageBody, Notification::class);
                $this->catchedNotification = $notification;
            }
        );

        // 送信するメッセージを作成する
        $message = TestRabbitMqMessageFactory::create();

        // when
        // メッセージを送信する
        $this->producer->send($message);

        // メッセージを受信する
        while ($this->queue->isSendingMessageToConsumer() && $this->catchedNotification === null) {
            $this->queue->wait(5);
        }

        // then
        // 適切にメッセージが受信されていることを確認する
        $this->assertEquals($message->toNotification(), $this->catchedNotification);
    }
}