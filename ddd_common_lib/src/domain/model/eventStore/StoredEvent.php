<?php

namespace dddCommonLib\domain\model\eventStore;

use DateTimeImmutable;
use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\domainEvent\DomainEvent;
use InvalidArgumentException;

class StoredEvent
{
    readonly string $eventBody;
    readonly string $occurredOn;
    readonly string $eventType;
    readonly string $eventId;

    private function __construct(
        string $anEventType, 
        string $anOccurredOn, 
        string $anEventBody,
        string $anEventId
    )
    {
        if (strtotime($anOccurredOn) === false) {
            throw new InvalidArgumentException('OccurredOn is not a valid date');
        }

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
        string $occurredOn,
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