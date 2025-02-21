<?php

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
        $this->producer = new KafkaProducer('kafka:9092', 'testTopic');
        $this->consumer = new TestConsumer(
            'testGroupId',
            'kafka:9092',
            'testTopic'
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

        try {
            $this->consumer->handle();
        } catch (Exception $ex) {
            // 何もしない
            print_r($ex->getMessage());
        }

        // then
        $this->assertContainsEquals($targetNotification1, $this->consumer->catchedMessageList);
        $this->assertContainsEquals($targetNotification2, $this->consumer->catchedMessageList);
        $this->assertNotContainsEquals($noTargetNotification, $this->consumer->catchedMessageList);
    }
}