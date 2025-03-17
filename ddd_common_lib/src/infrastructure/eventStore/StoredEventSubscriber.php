<?php

namespace dddCommonLib\infrastructure\eventStore;

use dddCommonLib\domain\model\domainEvent\DomainEvent;
use dddCommonLib\domain\model\domainEvent\DomainEventSubscriber;
use dddCommonLib\domain\model\eventStore\IEventStore;
use dddCommonLib\domain\model\eventStore\StoredEvent;

class StoredEventSubscriber implements DomainEventSubscriber
{
    private IEventStore $eventStore;

    public function __construct(IEventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function handleEvent(DomainEvent $domainEvent): void
    {
        $storedEvent = StoredEvent::fromDomainEvent($domainEvent);
        $this->eventStore->append($storedEvent);
    }

    public function subscribedToEventType(): string
    {
        return DomainEvent::class;
    }
}