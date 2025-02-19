<?php

namespace dddCommonLib\test\helpers\adapter\messaging\rabbitmq;

use dddCommonLib\infrastructure\messaging\rabbitmq\RabbitMqDeliveryMode;
use dddCommonLib\infrastructure\messaging\rabbitmq\RabbitMqMessage;
use dddCommonLib\domain\model\domainEvent\DomainEvent;
use dddCommonLib\domain\model\eventStore\StoredEvent;
use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;

class TestRabbitMqMessageFactory
{
    public static function create(
        ?DomainEvent $event = null,
        ?RabbitMqDeliveryMode $deliveryMode = null
    ): RabbitMqMessage
    {
        $testEvent = $event ?? new TestEvent();
        $deliveryMode = $deliveryMode ?? RabbitMqDeliveryMode::PERSISTENT;

        $storedEvent = StoredEvent::fromDomainEvent($testEvent);
        $notification = Notification::fromStoredEvent($storedEvent);
        return RabbitMqMessage::fromInstance($notification, $deliveryMode);
    }
}