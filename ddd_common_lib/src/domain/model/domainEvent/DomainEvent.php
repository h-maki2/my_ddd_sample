<?php

namespace dddCommonLib\domain\model\domainEvent;

use DateTimeImmutable;
use InvalidArgumentException;

abstract class DomainEvent
{
    protected string $occurredOn;
    protected int $eventVersion;

    public function __construct(int $eventVersion)
    {
        $occurredOn = new DateTimeImmutable();
        $this->occurredOn = $occurredOn->format('Y-m-d H:i:s');
        $this->eventVersion = $eventVersion;
    }

    public function occurredOn(): string
    {
        return $this->occurredOn;
    }

    public function eventVersion(): int
    {
        return $this->eventVersion;
    }

    abstract public function eventType(): string;
}