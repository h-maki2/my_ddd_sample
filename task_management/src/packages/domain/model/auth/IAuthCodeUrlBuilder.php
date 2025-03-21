<?php

namespace packages\domain\model\auth;

/**
 * 認可コードURL生成インターフェース
 */
interface IAuthCodeUrlBuilder
{
    public function authCodeUrlFrom(
        string $email,
        string $password
    ): string;
}