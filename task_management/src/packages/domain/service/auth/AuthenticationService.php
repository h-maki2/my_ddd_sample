<?php

namespace packages\domain\service\auth;

abstract class AuthenticationService
{
    protected const ONE_TIME_TOKEN_SESSION_KEY = 'one_time_session_key';

    abstract protected function setOneTimeToken(): void;

    abstract protected function deleteOneTimeToken(): void;

    abstract protected function clientId(): string;

    protected function createOneTimeToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}