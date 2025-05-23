<?php

namespace packages\domain\model\identityAccess\definitiveRegistration;

use packages\domain\model\definitiveRegistrationConfirmation\OneTimePassword;
use packages\domain\model\definitiveRegistrationConfirmation\OneTimeToken;
use packages\domain\model\authenticationAccount\UserEmail;
use packages\domain\model\definitiveRegistrationConfirmation\OneTimeTokenExpiration;
use packages\domain\model\email\SendEmailDto;

/**
 * 本登録済み更新メールDTOのファクトリ
 */
class DefinitiveRegistrationConfirmationEmailDtoFactory
{
    private const DefinitiveRegisterUrl = 'http://localhost:8080/definitive_register';
        
    public static function create(
        string $toEmailAddress,
        string $oneTimeTokenValue,
        string $oneTimePassword,
        string $expirationHours
    ): SendEmailDto
    {
        $templateValiables = [
            'definitiveRegisterUrl' => self::DefinitiveRegisterUrl . '?token=' . $oneTimeTokenValue,
            'oneTimePassword' => $oneTimePassword,
            'expirationHours' => $expirationHours . '時間後'
        ];
        return new SendEmailDto(
            'test@example.com',
            $toEmailAddress,
            'システムテスト',
            '本登録確認メール',
            'email.definitiveRegistration.definitiveRegistrationConfirmationMail',
            $templateValiables
        );
    }
}