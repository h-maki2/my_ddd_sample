<?php

use dddCommonLib\domain\model\common\IMessagingLogger;
use dddCommonLib\infrastructure\messaging\kafka\AKafkaConsumer;
use dddCommonLib\infrastructure\messaging\kafka\BrokerListener;
use dddCommonLib\infrastructure\messaging\kafka\KafkaProducer;
use dddCommonLib\infrastructure\messaging\kafka\MessageKafkaConsumer;
use dddCommonLib\test\helpers\adapter\messaging\kafka\BrokerListenFinishedException;
use dddCommonLib\test\helpers\adapter\messaging\kafka\KafkaCatchedTestMessageList;
use dddCommonLib\test\helpers\adapter\messaging\kafka\TestConsumer;
use dddCommonLib\test\helpers\adapter\messaging\kafka\TestNotificationBrokerListener;
use dddCommonLib\test\helpers\domain\model\event\NoTargetEvent;
use dddCommonLib\test\helpers\domain\model\event\OtherTestEvent;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use dddCommonLib\test\helpers\domain\model\notification\TestNotificationFactory;
use PhpParser\Node\Stmt\Block;
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
        $testBlokerListener = new TestNotificationBrokerListener($consumer, $this->messagingLogger, testable: true);

        $targetNotification1 = TestNotificationFactory::createFromDomainEvent(new TestEvent());
        $targetNotification2 = TestNotificationFactory::createFromDomainEvent(new OtherTestEvent());
        $noTargetNotification = TestNotificationFactory::createFromDomainEvent(new NoTargetEvent());

        $this->producer->send($noTargetNotification);
        $this->producer->send($targetNotification1);
        $this->producer->send($targetNotification2);

        // when
        try {
            $testBlokerListener->handle();
        } catch (BrokerListenFinishedException $ex) {
            // 何もしない
        }

        // then
        $this->assertContainsEquals($targetNotification1, $testBlokerListener->listenNotificationList);
        $this->assertContainsEquals($targetNotification2, $testBlokerListener->listenNotificationList);
        $this->assertNotContainsEquals($noTargetNotification, $testBlokerListener->listenNotificationList);
    }
}