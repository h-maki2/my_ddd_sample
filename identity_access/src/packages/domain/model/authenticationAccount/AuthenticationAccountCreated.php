<?php

namespace packages\domain\model\authenticationAccount;

use dddCommonLib\domain\model\domainEvent\DomainEvent;

class AuthenticationAccountCreated extends DomainEvent
{
    readonly string $userId;

    public function __construct(UserId $userId)
    {
        parent::__construct(1);
        $this->userId = $userId->value;
    }

    public function eventType(): string
    {
        return self::class;
    }
}