<?php

namespace dddCommonLib\domain\model\notification;

use DateTime;
use DateTimeImmutable;
use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\domainEvent\DomainEvent;
use dddCommonLib\domain\model\eventStore\StoredEvent;

class Notification
{
    readonly DomainEvent $domainEvent;
    readonly string $notificationId;
    readonly string $notificationType;
    readonly DateTimeImmutable $occurredOn;
    readonly int $version;

    private function __construct(
        DomainEvent $domainEvent,
        string $notificationId,
        string $notificationType,
        DateTimeImmutable $occurredOn,
        int $version
    ) {
        $this->domainEvent = $domainEvent;
        $this->notificationId = $notificationId;
        $this->notificationType = $notificationType;
        $this->occurredOn = $occurredOn;
        $this->version = $version;
    }

    public static function fromStoredEvent(StoredEvent $storedEvent): self
    {
        $domainEvent = $storedEvent->toDomainEvent();
        return new self(
            $domainEvent,
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
}