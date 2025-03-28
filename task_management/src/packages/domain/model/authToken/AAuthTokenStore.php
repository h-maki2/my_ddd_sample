<?php

namespace packages\domain\model\authToken;

abstract class AAuthTokenStore
{
    protected const ACCESS_TOKEN_KEY_NAME = 'access_token';

    protected const REFRESH_TOKEN_KEY_NAME = 'refresh_token';

    protected const TOKEN_EXPIRATION_KEY_NAME = 'token_expiration';

    protected const COOKIE_EXPIRATION_MINUTES = 120;

    abstract public function save(AuthToken $authToken): void;

    public function get(IAuthTokenService $authTokenService): ?AuthToken
    {
        $authToken = $this->getFromKeyName();
        if ($authToken === null) {
            return null;
        }

        if (!$authToken->accessTokenIsExpired()) {
            return $authToken;
        }

        $authToken = $authTokenService->refreshAuthToken(
            $authToken->refreshToken
        );
        return $authToken;
    }

    abstract public function clear(): void;

    abstract protected function getFromKeyName(): ?AuthToken;
}