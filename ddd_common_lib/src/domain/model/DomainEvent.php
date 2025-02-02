<?php

namespace dddCommonLib\domain\model;

use DateTimeImmutable;

interface DomainEvent
{
    public function occurredOn(): DateTimeImmutable;

    public function eventVersion(): int;

    public function eventType(): string;

    public function toStoredEvent(): StoredEvent;

    public function jsonSerialize(): string;
}