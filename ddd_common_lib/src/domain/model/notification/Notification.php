<?php

namespace dddCommonLib\domain\model\notification;

use DateTime;
use DateTimeImmutable;
use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\domainEvent\DomainEvent;
use dddCommonLib\domain\model\eventStore\StoredEvent;

class Notification
{
    readonly string $eventBody;
    readonly string $notificationId;
    readonly string $notificationType;
    readonly string $occurredOn;
    readonly int $version;

    private function __construct(
        string $eventBody,
        string $notificationId,
        string $notificationType,
        string $occurredOn,
        int $version
    ) {
        $this->eventBody = $eventBody;
        $this->notificationId = $notificationId;
        $this->notificationType = $notificationType;
        $this->occurredOn = $occurredOn;
        $this->version = $version;
    }

    public static function fromStoredEvent(StoredEvent $storedEvent): self
    {
        $domainEvent = $storedEvent->toDomainEvent();
        return new self(
            $storedEvent->eventBody,
            $storedEvent->eventId,
            $storedEvent->eventType,
            $storedEvent->occurredOn,
            $domainEvent->eventVersion()
        );
    }

    public function serialize(): string
    {
        return JsonSerializer::serialize($this);
    }

    public function toDomainEvent(): DomainEvent
    {
        return JsonSerializer::deserialize($this->eventBody, $this->notificationType);
    }
}