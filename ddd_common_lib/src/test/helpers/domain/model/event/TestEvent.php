<?php

namespace dddCommonLib\test\helpers\domain\model\event;

use DateTimeImmutable;
use dddCommonLib\domain\model\domainEvent\DomainEvent;

class TestEvent extends DomainEvent
{
    public function __construct()
    {
        parent::__construct(1);
    }

    public function eventType(): string
    {
        return self::class;
    }
}