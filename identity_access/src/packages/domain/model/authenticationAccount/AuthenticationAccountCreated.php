<?php

namespace packages\domain\model\authenticationAccount;

use dddCommonLib\domain\model\domainEvent\DomainEvent;

class AuthenticationAccountCreated extends DomainEvent
{
    readonly UserId $userId;

    public function __construct(UserId $userId)
    {
        $this->userId = $userId;
    }

    public function eventType(): string
    {
        return DomainEvent::class;
    }
}