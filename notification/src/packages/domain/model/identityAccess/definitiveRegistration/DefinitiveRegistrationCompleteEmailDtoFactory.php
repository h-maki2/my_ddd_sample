<?php

namespace packages\domain\model\identityAccess\definitiveRegistration;

use packages\domain\model\email\SendEmailDto;

class DefinitiveRegistrationCompleteEmailDtoFactory
{
    public static function create(
        string $toEmailAddress
    ): SendEmailDto
    {
        return new SendEmailDto(
            'test@example.com',
            $toEmailAddress,
            'システムテスト',
            '本登録完了メール',
            'email.definitiveRegistration.definitiveRegistrationCompleteMail',
            []
        );
    }
}