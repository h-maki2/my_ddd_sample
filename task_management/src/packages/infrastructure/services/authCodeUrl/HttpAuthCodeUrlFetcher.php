<?php

namespace packages\infrastructure\services\authCodeUrl;

use packages\domain\model\auth\IAuthCodeFetcher;

class HttpPassportAuthCodeUrlFetcher implements IAuthCodeFetcher
{
    public function authCodeUrlFrom(
        string $email,
        string $password,
        string $oneTimeToken
    ): string {

        $httpAuthCodeUrlAdapter = new HttpAuthCodeUrlAdapter();
        return $httpAuthCodeUrlAdapter->fetchAuthCodeUrl($email, $password, $oneTimeToken);
    }
}