<?php

namespace packages\domain\model\authToken;

abstract class AAuthTokenStore
{
    protected const ACCESS_TOKEN_KEY_NAME = 'access_token';

    protected const REFRESH_TOKEN_KEY_NAME = 'refresh_token';

    protected const TOKEN_EXPIRATION_KEY_NAME = 'token_expiration';

    protected const COOKIE_EXPIRATION_MINUTES = 120;

    abstract public function save(AuthToken $authToken): void;

    abstract public function get(): ?AuthToken;

    abstract public function clear(): void;
}