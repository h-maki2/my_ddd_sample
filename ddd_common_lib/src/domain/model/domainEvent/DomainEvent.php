<?php

namespace dddCommonLib\domain\model\domainEvent;

use DateTimeImmutable;

interface DomainEvent
{
    public function occurredOn(): DateTimeImmutable;

    public function eventVersion(): int;

    public function eventType(): string;
}