<?php

namespace dddCommonLib\test\helpers\event;

use DateTimeImmutable;
use dddCommonLib\domain\model\DomainEvent;

class TestEvent implements DomainEvent
{
    private DateTimeImmutable $occurredOn;
    private int $eventVersion;

    public function __construct()
    {
        $this->occurredOn = new DateTimeImmutable();
        $this->eventVersion = 1;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function eventVersion(): int
    {
        return $this->eventVersion;
    }

    public function eventType(): string
    {
        return self::class;
    }
}