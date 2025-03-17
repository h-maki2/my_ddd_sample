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