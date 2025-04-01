<?php

namespace packages\application\authenticationAccount\fetch;

class FetchAuthenticationAccountResult
{
    readonly string $userId;
    readonly string $userEmail;
    readonly bool $accountExists;

    private function __construct(string $userId, string $userEmail, bool $accountExists)
    {
        $this->userId = $userId;
        $this->userEmail = $userEmail;
        $this->accountExists = $accountExists;
    }

    public static function createWhenAccountExists(string $userId, string $userEmail): self
    {
        return new self($userId, $userEmail, true);
    }

    public static function createWhenAccountNotExists(): self
    {
        return new self('', '', false);
    }
}