<?php

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\infrastructure\messaging\kafka\KafkaConsumer;
use dddCommonLib\infrastructure\messaging\kafka\KafkaProducer;
use dddCommonLib\test\helpers\adapter\messaging\kafka\KafkaCatchedTestMessageList;
use dddCommonLib\test\helpers\adapter\messaging\kafka\TestConsumer;
use dddCommonLib\test\helpers\domain\model\event\NoTargetEvent;
use dddCommonLib\test\helpers\domain\model\event\OtherTestEvent;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use dddCommonLib\test\helpers\domain\model\notification\TestNotificationFactory;
use PHPUnit\Framework\TestCase;

class KafkaConsumerTest extends TestCase
{
    private KafkaProducer $producer;
    private TestConsumer $consumer;
    
    public function setUp(): void
    {
        $messagingLoggerMock = $this->createMock(IMessagingLogger::class);

        $this->producer = new KafkaProducer('kafka:9092', 'testTopic');
        $this->consumer = new TestConsumer(
            'testGroupId',
            'kafka:9092',
            'testTopic',
            $messagingLoggerMock,
            testable: true
        );
    }

    public function test_プロデューサから送信したメッセージを受信できることを確認する()
    {
        // given
        $targetNotification1 = TestNotificationFactory::createFromDomainEvent(new TestEvent());
        $targetNotification2 = TestNotificationFactory::createFromDomainEvent(new OtherTestEvent());
        $noTargetNotification = TestNotificationFactory::createFromDomainEvent(new NoTargetEvent());

        $this->producer->send($noTargetNotification);
        $this->producer->send($targetNotification1);
        $this->producer->send($targetNotification2);

        // when
        try {
            $this->consumer->handle();
        } catch (Exception $ex) {
            // 何もしない
            print($ex->getMessage());
        }

        // then
        // 対象のイベントをリッスンできていることを確認する
        $this->assertTrue($this->consumer->listenEvent(TestEvent::class));
        $this->assertTrue($this->consumer->listenEvent(OtherTestEvent::class));
        $this->assertFalse($this->consumer->listenEvent(NoTargetEvent::class));
    }
}