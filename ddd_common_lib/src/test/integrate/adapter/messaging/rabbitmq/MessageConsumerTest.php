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
    public function test_コンシューマーでメッセージを受信できる()
    {
        // given
        $testExchangeName = 'test_exchange';
        $testConnection = new ConnectionSettings('localhost', 5672, 'user', 'password');
        $testQueue = 'test_queue';

        // エクスチェンジを作成する
        $exchange = Exchange::fanOutInstance(
            $testConnection,
            $testExchangeName,
            true
        );

        // プロデューサーを作成する
        $producer = new MessageProducer($exchange);

        // キューを作成する
        $queue = RabbitMqQueue::fromInstance(
            $testConnection,
            $testQueue,
            true
        );

        // コンシューマを作成する
        $messageBody = null;
        $consumer = new MessageConsumer(
            $queue,
            $testExchangeName,
            [],
            function (Notification $notification) use (&$messageBody) {
                $messageBody = $notification;
            }
        );

        // 送信するメッセージを作成する
        $testEvent = new TestEvent('test message');
        $storedEvent = StoredEvent::fromDomainEvent($testEvent);
        $notification = Notification::fromStoredEvent($storedEvent);
        $message = RabbitMqMessage::get($notification->serialize(), RabbitMqDeliveryMode::PERSISTENT);

        // when
        // メッセージを送信する
        $producer->send($message);

        // メッセージを受信する
        while ($consumer->channel()->is_consuming() && $messageBody === null) {
            $consumer->channel()->wait(null, false, 5); // 5秒でタイムアウト
        }
    
        $consumer->close();

        // then
        $this->assertNotNull($messageBody);
    }
}