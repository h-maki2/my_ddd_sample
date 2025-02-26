<?php

namespace packages\domain\model\authenticationAccount;

use dddCommonLib\domain\model\domainEvent\DomainEvent;

class AuthenticationAccountCreated extends DomainEvent
{
    readonly UserId $userId;
    readonly UserEmail $email;

    public function __construct(UserId $userId, UserEmail $email)
    {
        $this->userId = $userId;
        $this->email = $email;
    }

    public function eventType(): string
    {
        return DomainEvent::class;
    }
}