<?php

use dddCommonLib\adapter\messaging\rabbitmq\Exchange;
use dddCommonLib\adapter\messaging\rabbitmq\MessageProducer;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqQueue;
use dddCommonLib\domain\model\domainEvent\DomainEvent;
use dddCommonLib\test\helpers\adapter\messaging\rabbitmq\TestExchangeListener;
use dddCommonLib\test\helpers\adapter\messaging\rabbitmq\TestRabbitMqMessageFactory;
use dddCommonLib\test\helpers\domain\model\event\OtherTestEvent;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use PHPUnit\Framework\TestCase;

class ExchangeListenerTest extends TestCase
{
    private TestExchangeListener $listener;
    private RabbitMqQueue $queue;
    private MessageProducer $producer;

    public function setUp(): void
    {
        $this->listener = new TestExchangeListener();

        $this->queue = $this->listener->queue();

        $exchange = Exchange::fanOutInstance(
            $this->listener->connectionSettings(),
            $this->listener->exchangeName(),
            true
        );
        $this->producer = new MessageProducer($exchange);
    }

    public function tearDown(): void
    {
        $this->queue->channel->queue_delete($this->listener->queueName());
        $this->queue->close();
    }

    public function test_対象のイベントを受信できる()
    {
        // given
        $受信対象のメッセージ1 = TestRabbitMqMessageFactory::create(
            new TestEvent()
        );

        $受信対象のメッセージ2 = TestRabbitMqMessageFactory::create(
            new OtherTestEvent()
        );

        $受信対象外のメッセージ = TestRabbitMqMessageFactory::create(
            new NotTargetEvent()
        );

        // メッセージを送信する
        $this->producer->send($受信対象のメッセージ1);
        $this->producer->send($受信対象のメッセージ2);
        $this->producer->send($受信対象外のメッセージ);

        // when
        // メッセージを受信する
        $eventCount = 3;
        $this->listener->testHandle($eventCount);

        // then
        // 対象のイベントが受信されていることを確認
        $this->assertContains(TestEvent::class, $this->listener->handledEventList());
        $this->assertContains(OtherTestEvent::class, $this->listener->handledEventList());
        $this->assertNotContains(NotTargetEvent::class, $this->listener->handledEventList());
    }
}

class NotTargetEvent extends DomainEvent
{
    public function __construct()
    {
        parent::__construct(1);
    }

    public function eventType(): string
    {
        return self::class;
    }
}