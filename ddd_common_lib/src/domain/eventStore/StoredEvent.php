<?php

namespace dddCommonLib\domain\eventStore;

use DateTimeImmutable;
use dddCommonLib\domain\model\DomainEvent;

class StoredEvent
{
    readonly string $eventBody;
    readonly DateTimeImmutable $occurredOn;
    readonly string $eventType;

    private function __construct(
        string $anEventType, 
        DateTimeImmutable $anOccurredOn, 
        string $anEventBody
    )
    {
        $this->eventType = $anEventType;
        $this->occurredOn = $anOccurredOn;
        $this->eventBody = $anEventBody;
    }

    public static function fromDomainEvent(DomainEvent $aDomainEvent): self
    {
        $eventBody = JsonSerializer::serialize($aDomainEvent);
        return new self(
            $aDomainEvent->eventType(),
            $aDomainEvent->occurredOn(),
            $eventBody
        );
    }

    public static function fromJsonArray(array $jsonArray): self
    {
        return new StoredEvent(
            $jsonArray['eventType'],
            new DateTimeImmutable($jsonArray['occurredOn']),
            $jsonArray['eventBody']
        );
    }

    public function serialize(): string
    {
        return JsonSerializer::serialize($this);
    }

    public function toDomainEvent(): DomainEvent
    {
        return JsonSerializer::deserialize($this->eventBody, $this->eventType);
    }
}