<?php

namespace dddCommonLib\test\helpers\domain\model\notification;

use dddCommonLib\domain\model\domainEvent\DomainEvent;
use dddCommonLib\domain\model\eventStore\StoredEvent;
use dddCommonLib\domain\model\notification\Notification;

class TestNotificationFactory
{
    public static function createFromDomainEvent(DomainEvent $domainEvent): Notification
    {
        $storedEvent = StoredEvent::fromDomainEvent($domainEvent);
        return Notification::fromStoredEvent($storedEvent);
    }
}