<?php

namespace packages\domain\model\authToken;

use packages\tests\domain\model\authToken\RefreshToken;

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

    /**
     * アクセストークンの有効期限が切れているかどうかを判定
     */
    public function accessTokenIsExpired(): bool
    {
        return $this->accessToken->isExpired();
    }
}