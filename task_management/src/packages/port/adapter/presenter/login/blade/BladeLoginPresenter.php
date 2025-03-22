<?php

namespace packages\port\adapter\presenter\login\blade;

use packages\application\login\LoginResult;

class BladeLoginPresenter
{
    private LoginResult $loginResult;

    public function __construct(LoginResult $loginResult)
    {
        $this->loginResult = $loginResult;
    }

    public function loginSuccess(): bool
    {
        return $this->loginResult->isSuccess;
    }

    public function authenticationRequestUrl(): string
    {
        return $this->loginResult->authorizationRequestUrl;
    }

    public function faildMessage(): string
    {
        if ($this->loginResult->accountLocked) {
            return "アカウントがロックされています。\n10分後に再度お試しください。";
        }

        if (!$this->loginResult->isSuccess) {
            return "ログインに失敗しました。\n入力内容をご確認ください。";
        }

        return "";
    }
}