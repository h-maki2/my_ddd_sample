<?php

namespace packages\application\definitiveRegistration;

use packages\domain\model\email\IEmailSender;
use packages\domain\model\identityAccess\definitiveRegistration\DefinitiveRegistrationCompleteEmailDtoFactory;

/**
 * 本登録完了メール送信アプリケーションサービス
 */
class SendDefinitiveRegistrationCompleteEmailApplicationService
{
    private IEmailSender $emailSender;

    public function __construct(IEmailSender $emailSender)
    {
        $this->emailSender = $emailSender;
    }

    public function handle(
        string $email
    ): void
    {
        $this->emailSender->send(
            DefinitiveRegistrationCompleteEmailDtoFactory::create(
                $email
            )
        );
    }
}