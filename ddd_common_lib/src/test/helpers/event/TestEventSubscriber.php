<?php

namespace dddCommonLib\test\helpers\event;

use dddCommonLib\domain\model\domainEvent\DomainEvent;
use dddCommonLib\domain\model\domainEvent\DomainEventSubscriber;

class TestEventSubscriber implements DomainEventSubscriber
{
    public bool $handled = false;

    public function handleEvent(DomainEvent $aDomainEvent): void
    {
        $this->handled = true;
    }

    public function isSubscribedTo(DomainEvent $aDomainEvent): bool
    {
        return $this->subscribedToEventType() === $aDomainEvent->eventType();
    }

    public function subscribedToEventType(): string
    {
        return TestEvent::class;
    }
}