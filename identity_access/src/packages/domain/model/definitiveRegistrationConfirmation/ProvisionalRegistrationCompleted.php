<?php

namespace packages\domain\model\definitiveRegistrationConfirmation;

use dddCommonLib\domain\model\domainEvent\DomainEvent;
use packages\domain\model\authenticationAccount\UserEmail;

class ProvisionalRegistrationCompleted extends DomainEvent
{
    readonly string $email;
    readonly string $oneTimeToken;
    readonly string $oneTimePassword;
    readonly string $expirationHours;

    public function __construct(
        OneTimeToken $oneTimeToken,
        OneTimePassword $oneTimePassword,
        UserEmail $email
    )
    {
        parent::__construct(1);
        $this->email = $email->value;
        $this->oneTimeToken = $oneTimeToken->tokenValue()->value;
        $this->oneTimePassword = $oneTimePassword->value;
        $this->expirationHours = OneTimeTokenExpiration::expirationHours();
    }

    public function eventType(): string
    {
        return DomainEvent::class;
    }
}