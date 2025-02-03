<?php

namespace dddCommonLib\domain\model\eventStore;

use DateTimeImmutable;
use dddCommonLib\domain\model\domainEvent\DomainEvent;

class StoredEvent
{
    readonly string $eventBody;
    readonly DateTimeImmutable $occurredOn;
    readonly string $eventType;
    readonly string $eventId;

    private function __construct(
        string $anEventType, 
        DateTimeImmutable $anOccurredOn, 
        string $anEventBody,
        string $anEventId
    )
    {
        $this->eventType = $anEventType;
        $this->occurredOn = $anOccurredOn;
        $this->eventBody = $anEventBody;
        $this->eventId = $anEventId;
    }

    public static function fromDomainEvent(DomainEvent $aDomainEvent): self
    {
        $eventBody = JsonSerializer::serialize($aDomainEvent);
        return new self(
            $aDomainEvent->eventType(),
            $aDomainEvent->occurredOn(),
            $eventBody,
            '0'
        );
    }

    public static function reconstruct(
        string $eventType,
        DateTimeImmutable $occurredOn,
        string $eventBody,
        string $eventId
    ): self
    {
        return new StoredEvent(
            $eventType,
            $occurredOn,
            $eventBody,
            $eventId
        );
    }

    public function toDomainEvent(): DomainEvent
    {
        return JsonSerializer::deserialize($this->eventBody, $this->eventType);
    }
}