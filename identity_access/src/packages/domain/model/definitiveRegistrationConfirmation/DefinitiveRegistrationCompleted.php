<?php

namespace packages\domain\model\definitiveRegistrationConfirmation;

use dddCommonLib\domain\model\domainEvent\DomainEvent;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\authenticationAccount\UserId;

/**
 * 本登録完了済みイベント
 */
class DefinitiveRegistrationCompleted extends DomainEvent
{
    readonly string $userId;
    readonly string $email;

    public function __construct(
        UserId $userId,
        UserEmail $email
    )
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