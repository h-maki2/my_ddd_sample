<?php

namespace packages\domain\model\auth;

/**
 * 認可リクエスト用のURLを生成するクラス
 */
interface IAuthorizationRequestUrlBuildService
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