<?php

namespace packages\domain\model\authToken;

class AuthToken
{
    readonly AccessToken $accessToken;
    readonly RefreshToken $refreshToken;

    public function __construct(AccessToken $accessToken, RefreshToken $refreshToken)
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    /**
     * アクセストークンの有効期限が切れているかどうかを判定
     */
    public function accessTokenIsExpired(): bool
    {
        return $this->accessToken->isExpired();
    }
}