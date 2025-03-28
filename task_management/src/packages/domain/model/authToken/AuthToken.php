<?php

namespace packages\domain\model\authToken;

class AuthToken
{
    private AccessToken $accessToken;
    private RefreshToken $refreshToken;

    public function __construct(AccessToken $accessToken, RefreshToken $refreshToken)
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    public function accessToken(): string
    {
        return $this->accessToken->value;
    }

    public function refreshToken(): string
    {
        return $this->refreshToken->value;
    }

    public function accessTokenExpiresIn(): int
    {
        return $this->accessToken->expiresIn();
    }

    /**
     * アクセストークンの有効期限が切れているかどうかを判定
     */
    public function accessTokenIsExpired(): bool
    {
        return $this->accessToken->isExpired();
    }
}