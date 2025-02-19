<?php

use dddCommonLib\infrastructure\messaging\rabbitmq\ConnectionSettings;
use dddCommonLib\infrastructure\notification\rabbitmq\RabbitMQNotificationPublisher;
use dddCommonLib\domain\model\eventStore\IEventStoreLogService;
use dddCommonLib\domain\model\eventStore\StoredEvent;
use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\domain\model\notification\PublishedNotificationTracker;
use dddCommonLib\test\helpers\adapter\messaging\rabbitmq\TestExchangeListener;
use dddCommonLib\test\helpers\adapter\messaging\rabbitmq\TestExchangeName;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use dddCommonLib\test\helpers\domain\model\eventStore\InMemoryEventStore;
use dddCommonLib\test\helpers\domain\model\notification\InMemoryPublishedNotificationTrackerStore;
use PHPUnit\Framework\TestCase;

class ExchangeListenerTest extends TestCase
{
    private InMemoryEventStore $eventStore;
    private InMemoryPublishedNotificationTrackerStore $publishedNotificationTrackerStore;
    private RabbitMQNotificationPublisher $publisher;
    private TestExchangeListener $listener;

    public function setUp(): void
    {
        $this->eventStore = new InMemoryEventStore();
        $this->publishedNotificationTrackerStore = new InMemoryPublishedNotificationTrackerStore();

        $logService = $this->createMock(IEventStoreLogService::class);
        $connectionSettings = new ConnectionSettings('rabbitmq', 'user', 'password', 5672);
        $this->publisher = new RabbitMQNotificationPublisher(
            $connectionSettings,
            TestExchangeName::TEST_EXCHANGE_NAME->value,
            $this->publishedNotificationTrackerStore,
            $this->eventStore,
            $logService
        );

        $this->listener = new TestExchangeListener();
    }

    public function tearDown(): void
    {
        $this->listener->deleteQueue();
    }

    public function test_プロデューサーがメッセージをエクスチェンジに送信すると、コンシェーマがそのメッセージを受信できることを確認する()
    {
        // given
        // 送信済みのメッセージを作成して保存する
        $送信済みのイベント1 = new TestEvent();
        $送信済みメッセージ1 = StoredEvent::fromDomainEvent($送信済みのイベント1);
        $this->eventStore->append($送信済みメッセージ1);
        sleep(1);

        $送信済みのイベント2 = new TestEvent();
        $送信済みメッセージ2 = StoredEvent::fromDomainEvent($送信済みのイベント2);
        $this->eventStore->append($送信済みメッセージ2);
        sleep(1);

        // 送信済みの最新のメッセージIDを保存する
        $this->publishedNotificationTrackerStore->save(
            new PublishedNotificationTracker('2')
        );

        // 送信するメッセージを作成
        $送信対象のイベント1 = new TestEvent();
        $送信するメッセージ1 = StoredEvent::fromDomainEvent($送信対象のイベント1);
        $this->eventStore->append($送信するメッセージ1);
        sleep(1);

        $送信対象のイベント2 = new TestEvent();
        $送信するメッセージ2 = StoredEvent::fromDomainEvent($送信対象のイベント2);
        $this->eventStore->append($送信するメッセージ2);
        sleep(1);

        // when
        // プロデューサーが未送信のメッセージをエクスチェンジに送信する
        $this->publisher->publishNotifications();

        // コンシェーマがメッセージを受信する
        $this->listener->testHandle(2);

        // then
        // 送信したメッセージをコンシェーマで受信できていることを確認する
        $受信したメッセージ1 = Notification::fromStoredEvent($送信するメッセージ1);
        $受信したメッセージ2 = Notification::fromStoredEvent($送信するメッセージ2);

        $受信していないメッセ―ジ = Notification::fromStoredEvent($送信済みメッセージ1);

        $this->assertTrue($this->listener->hasHandledNotification($受信したメッセージ1));
        $this->assertTrue($this->listener->hasHandledNotification($受信したメッセージ2));
        $this->assertFalse($this->listener->hasHandledNotification($受信していないメッセ―ジ));
    }
}