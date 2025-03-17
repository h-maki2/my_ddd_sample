<?php

namespace packages\application\definitiveRegistrationConfirmation;

use packages\domain\model\email\IEmailSender;
use packages\domain\model\identityAccess\definitiveRegistrationConfirmation\DefinitiveRegistrationConfirmationEmailDtoFactory;

/**
 * 本登録確認メール送信アプリケーションサービス
 */
class SendDefinitiveRegistrationConfirmationEmailApplicationService
{
    private IEmailSender $emailSender;

    public function __construct(IEmailSender $emailSender)
    {
        $this->emailSender = $emailSender;
    }

    public function handle(
        string $oneTimeTokenValue,
        string $oneTimePassword,
        string $expirationHours,
        string $email
    ): void
    {
        $this->emailSender->send(
            DefinitiveRegistrationConfirmationEmailDtoFactory::create(
                $email,
                $oneTimeTokenValue,
                $oneTimePassword,
                $expirationHours
            )
        );
    }
}