<?php

namespace packages\application\login;

class LoginResult
{
    readonly string $authorizationRequestUrl;
    readonly bool $isSuccess;
    readonly bool $accountLocked;

    private function __construct(
        string $authorizationRequestUrl,
        bool $isSuccess,
        bool $accountLocked
    )
    {
        $this->authorizationRequestUrl = $authorizationRequestUrl;
        $this->isSuccess = $isSuccess;
        $this->accountLocked = $accountLocked;
    }

    public static function createWhenSuccess(string $authorizationRequestUrl): self
    {
        return new self($authorizationRequestUrl, true, false);
    }

    public static function createWhenFailure(
        bool $accountLocked
    ): self
    {
        return new self('', false, $accountLocked);
    }
}