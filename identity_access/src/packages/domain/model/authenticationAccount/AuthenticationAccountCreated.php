<?php

namespace packages\domain\model\authenticationAccount;

use dddCommonLib\domain\model\domainEvent\DomainEvent;

class AuthenticationAccountCreated extends DomainEvent
{
    readonly string $userId;
    readonly string $email;

    public function __construct(UserId $userId, UserEmail $email)
    {
        parent::__construct(1);
        $this->userId = $userId->value;
        $this->email = $email->value;
    }

    public function eventType(): string
    {
        return self::class;
    }
}