<?php

use dddCommonLib\adapter\messaging\rabbitmq\ConnectionSettings;
use dddCommonLib\adapter\notification\rabbitmq\RabbitMQNotificationPublisher;
use dddCommonLib\domain\model\eventStore\IEventStore;
use dddCommonLib\domain\model\eventStore\IEventStoreLogService;
use dddCommonLib\domain\model\eventStore\StoredEvent;
use dddCommonLib\domain\model\notification\PublishedNotificationTracker;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use dddCommonLib\test\helpers\domain\model\eventStore\InMemoryEventStore;
use dddCommonLib\test\helpers\domain\model\notification\InMemoryPublishedNotificationTrackerStore;
use PHPUnit\Framework\TestCase;

class RabbitMQNotificationPublisherTest extends TestCase
{
    private InMemoryEventStore $eventStore;
    private RabbitMQNotificationPublisher $publisher;
    private InMemoryPublishedNotificationTrackerStore $publishedNotificationTrackerStore;
    private IEventStoreLogService $eventStoreLogService;

    public function setUp(): void
    {
        $this->eventStore = new InMemoryEventStore();
        $this->publishedNotificationTrackerStore = new InMemoryPublishedNotificationTrackerStore();

        $eventStoreLogServiceMock = $this->createMock(IEventStoreLogService::class);

        $testExchangeName = 'test_exchange';
        $testConnectionSettings = new ConnectionSettings('rabbitmq', 'user', 'password', 5672);

        $this->publisher = new RabbitMQNotificationPublisher(
            $testConnectionSettings,
            $testExchangeName,
            $this->publishedNotificationTrackerStore,
            $this->eventStore,
            $eventStoreLogServiceMock
        );
    }

    public function test_プロデューサがエクスチェンジにメッセージを送信した場合に、送信済みの最新のメッセージのIDを保存できる()
    {
        // given
        // イベントを作成して保存する
        $event = new TestEvent();
        $storedEvent1 = StoredEvent::fromDomainEvent($event);
        $this->eventStore->append($storedEvent1);

        $storedEvent2 = StoredEvent::fromDomainEvent($event);
        $this->eventStore->append($storedEvent2);

        $storedEvent3 = StoredEvent::fromDomainEvent($event);
        $this->eventStore->append($storedEvent3);

        $storedEvent4 = StoredEvent::fromDomainEvent($event);
        $this->eventStore->append($storedEvent4);

        // 送信済みの最新のメッセージIDを保存する
        $this->publishedNotificationTrackerStore->save(
            new PublishedNotificationTracker('2')
        );

        // when
        $this->publisher->publishNotifications();

        // then
        // 送信済みの最新のメッセージIDが保存されていることを確認する
        $publishedNotificationTracker = $this->publishedNotificationTrackerStore->publishedNotificationTracker();
        $this->assertEquals('4', $publishedNotificationTracker->mostResentPublishedNotificationId());
    }
}