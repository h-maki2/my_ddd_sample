<?php

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\infrastructure\messaging\kafka\AKafkaConsumer;
use dddCommonLib\infrastructure\messaging\kafka\BrokerListener;
use dddCommonLib\infrastructure\messaging\kafka\KafkaProducer;
use dddCommonLib\infrastructure\messaging\kafka\MessageKafkaConsumer;
use dddCommonLib\test\helpers\adapter\messaging\kafka\KafkaCatchedTestMessageList;
use dddCommonLib\test\helpers\adapter\messaging\kafka\TestConsumer;
use dddCommonLib\test\helpers\adapter\messaging\kafka\TestNotificationBrokerListener;
use dddCommonLib\test\helpers\domain\model\event\NoTargetEvent;
use dddCommonLib\test\helpers\domain\model\event\OtherTestEvent;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use dddCommonLib\test\helpers\domain\model\notification\TestNotificationFactory;
use PHPUnit\Framework\TestCase;

class NotificationBrokerListenerTest extends TestCase
{
    private KafkaProducer $producer;
    private IMessagingLogger $messagingLogger;
    
    public function setUp(): void
    {
        $this->messagingLogger = $this->createMock(IMessagingLogger::class);

        $this->producer = new KafkaProducer('kafka:9092', 'testTopic');
    }

    public function test_プロデューサから送信したメッセージを受信できることを確認する()
    {
        // given
        // コンシューマを作成する
        $consumer = new MessageKafkaConsumer(
            'testGroupId',
            'kafka:9092',
            ['testTopic'],
        );

        // テスト用のリスナーを作成する
        $testBlokerListener = new TestNotificationBrokerListener($consumer, $this->messagingLogger);

        $targetNotification1 = TestNotificationFactory::createFromDomainEvent(new TestEvent());
        $targetNotification2 = TestNotificationFactory::createFromDomainEvent(new OtherTestEvent());
        $noTargetNotification = TestNotificationFactory::createFromDomainEvent(new NoTargetEvent());

        $this->producer->send($noTargetNotification);
        $this->producer->send($targetNotification1);
        $this->producer->send($targetNotification2);

        // when
        $result = $this->captureConsoleOutput(function () use ($testBlokerListener) {
            $testBlokerListener->handle();
        });

        // then
        $this->assertStringContainsString('全てのイベントがリッスンされました', $result);
    }

    private function captureConsoleOutput(callable $function)
    {
        ob_start();
        $function();
        return ob_get_clean();
    }
}