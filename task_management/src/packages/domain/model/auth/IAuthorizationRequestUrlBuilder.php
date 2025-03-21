<?php

namespace packages\domain\model\auth;

/**
 * 認可リクエスト用のURLを生成するクラス
 */
interface IAuthorizationRequestUrlBuilder
{
    /**
     * 認可リクエスト用のURLを生成
     */
    public function build(
        string $email,
        string $password,
        string $oneTimeToken
    ): string;
}