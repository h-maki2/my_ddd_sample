<?php

namespace dddCommonLib\adapter\notification\rabbitmq;

use dddCommonLib\adapter\messaging\rabbitmq\ConnectionSettings;
use dddCommonLib\adapter\messaging\rabbitmq\Exchange;
use dddCommonLib\adapter\messaging\rabbitmq\MessageProducer;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqDeliveryMode;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqMessage;
use dddCommonLib\domain\model\eventStore\IEventStore;
use dddCommonLib\domain\model\eventStore\IEventStoreLogService;
use dddCommonLib\domain\model\eventStore\StoredEvent;
use dddCommonLib\domain\model\notification\IPublishedNotificationTrackerStore;
use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\domain\model\notification\PublishedNotificationTracker;
use Exception;

class RabbitMQNotificationPublisher
{
    private MessageProducer $messageProducer;
    private MessageProducer $dlxMessageProducer;
    private IPublishedNotificationTrackerStore $publishedNotificationTrackerStore;
    private IEventStore $eventStore;
    private IEventStoreLogService $eventStoreLogService;

    public function __construct(
        ConnectionSettings $connectionSettings, 
        string $exchangeName,
        IPublishedNotificationTrackerStore $publishedNotificationTrackerStore,
        IEventStore $eventStore,
        IEventStoreLogService $eventStoreLogService
    )
    {
        $this->attachToMessageProducer($connectionSettings, $exchangeName);
        $this->attachToDlxMessageProducer($connectionSettings);
        $this->publishedNotificationTrackerStore = $publishedNotificationTrackerStore;
        $this->eventStore = $eventStore;
        $this->eventStoreLogService = $eventStoreLogService;
    }

    public function publishNotifications(): void
    {
        $publishedNotificationTracker = $this->publishedNotificationTracker();
        $storedEventList = $this->eventStore->allStoredEventsSince($publishedNotificationTracker->mostResentPublishedNotificationId());

        foreach ($storedEventList as $storedEvent) {
            $notification = Notification::fromStoredEvent($storedEvent);
            try {
                $this->publishMessage(
                    RabbitMqMessage::fromInstance($notification, RabbitMqDeliveryMode::PERSISTENT)
                );
            } catch (Exception $e) {
                $this->eventStoreLogService->log($storedEvent, $e->getMessage());
            }
        }

        $this->trackMostRecentPublishedNotification($storedEventList, $publishedNotificationTracker);

        $this->messageProducer->close();
        $this->dlxMessageProducer->close();
    }

    private function attachToMessageProducer(ConnectionSettings $connectionSettings, string $exchangeName): void
    {
        $exchange = Exchange::fanOutInstance(
            $connectionSettings,
            $exchangeName,
            true
        );

        $this->messageProducer = new MessageProducer($exchange);
    }

    private function attachToDlxMessageProducer(ConnectionSettings $connectionSettings): void
    {
        $exchange = Exchange::dlxInstance($connectionSettings);

        $this->dlxMessageProducer = new MessageProducer($exchange);
    }

    private function publishedNotificationTracker(): PublishedNotificationTracker
    {
        return $this->publishedNotificationTrackerStore->publishedNotificationTracker() ?? 
            PublishedNotificationTracker::initialize();
    }

    /**
     * @param StoredEvent[] $storedEventList
     */
    private function trackMostRecentPublishedNotification(array $storedEventList, PublishedNotificationTracker $publishedNotificationTracker): void
    {
        $lastStoredEvent = end($storedEventList);
        $publishedNotificationTracker->updatePublishedNotificationId($lastStoredEvent->storedEventId);
        $this->publishedNotificationTrackerStore->save($publishedNotificationTracker);
    }

    private function publishMessage(RabbitMqMessage $message): void
    {
        try {
            $this->messageProducer->send($message);
        } catch (Exception $e) {
            $this->dlxMessageProducer->send($message);
        }
    }
}