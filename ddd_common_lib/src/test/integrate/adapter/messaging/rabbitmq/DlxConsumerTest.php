<?php

use dddCommonLib\adapter\messaging\rabbitmq\ConnectionSettings;
use dddCommonLib\adapter\messaging\rabbitmq\DlxConsumer;
use dddCommonLib\adapter\messaging\rabbitmq\Exchange;
use dddCommonLib\adapter\messaging\rabbitmq\MessageConsumer;
use dddCommonLib\adapter\messaging\rabbitmq\MessageProducer;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqMessage;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqQueue;
use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\test\helpers\adapter\messaging\rabbitmq\InMemoryRabbitMqLogService;
use dddCommonLib\test\helpers\adapter\messaging\rabbitmq\TestExchangeName;
use dddCommonLib\test\helpers\adapter\messaging\rabbitmq\TestQueueName;
use dddCommonLib\test\helpers\adapter\messaging\rabbitmq\TestRabbitMqMessageFactory;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;

class DlxConsumerTest extends TestCase
{
    private RabbitMqQueue $queue;
    private string $testExchangeName;
    private string $testQueueName;
    private MessageProducer $producer;
    private RabbitMqQueue $dlxQueue;
    private MessageConsumer $consumer;
    private Exchange $dlxExchange;
    private InMemoryRabbitMqLogService $logService;
    private ?RabbitMqMessage $catchedMessage;
    private ?Notification $catchedNotification;

    public function setUp(): void
    {
        $testConnection = new ConnectionSettings('rabbitmq', 'user', 'password', 5672);

        $this->testExchangeName = TestExchangeName::TEST_EXCHANGE_NAME->value;
        $this->testQueueName = TestQueueName::TEST_QUEUE_NAME->value;

        // エクスチェンジを作成する
        $exchange = Exchange::fanOutInstance(
            $testConnection,
            $this->testExchangeName,
            true
        );

        // DLX用のエクスチェンジを作成する
        $this->dlxExchange = Exchange::dlxInstance(
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

        // DLX用のキューを作成する
        $this->dlxQueue = RabbitMqQueue::declareDlxQueue(
            $this->dlxExchange
        );
    }

    public function tearDown(): void
    {
        $this->queue->delete();
        $this->queue->close();

        $this->dlxQueue->delete();
        $this->dlxQueue->close();
    }

    public function test_コンシェーマのメッセージ受信に3回失敗した場合、DLXにメッセージが転送される()
    {
        // given
        // バックグランドでコンシューマを起動する
        exec('php src/test/integrate/adapter/messaging/rabbitmq/consumerInBackgroundProcess.php > output.log 2>&1 &');

        sleep(2);

        // DLX用のコンシェーマを作成する
        $this->catchedNotification = null;
        $dlxConsumer = new DlxConsumer(
            $this->dlxQueue,
            $this->dlxExchange->exchangeName,
            [],
            function (string $messageBody) {
                $notification = JsonSerializer::deserialize($messageBody, Notification::class);
                $this->catchedNotification = $notification;
            }
        );

        // 送信するメッセージを作成する
        $message = TestRabbitMqMessageFactory::create();

        // メッセージを送信する
        $this->producer->send($message);

        // when
        while ($this->dlxQueue->isSendingMessageToConsumer() && $this->catchedNotification === null) {
            $this->dlxQueue->wait(5);
        }

        // then
        // 送信したメッセージと受信したメッセージが一致していることを確認する
        $expectedNotification = JsonSerializer::deserialize($message->messageBody(), Notification::class); 
        $this->assertEquals($expectedNotification , $this->catchedNotification);
    }
}