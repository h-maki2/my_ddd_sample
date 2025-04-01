<?php

namespace packages\application\authenticationAccount\fetch;

class FetchAuthenticationAccountResult
{
    readonly string $userId;
    readonly string $userEmail;

    public function __construct(string $userId, string $userEmail)
    {
        $this->userId = $userId;
        $this->userEmail = $userEmail;
    }
}