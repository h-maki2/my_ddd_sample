<?php

namespace packages\domain\model\auth;

/**
 * 認可コードURL取得インターフェース
 */
interface IAuthCodeUrlFetcher
{
    /**
     * 認可コードURLを取得する
     */
    public function authCodeUrlFrom(
        string $email,
        string $password
    ): string;
}