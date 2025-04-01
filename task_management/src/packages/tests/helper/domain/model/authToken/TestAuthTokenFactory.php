<?php

namespace packages\tests\helper\domain\model\authToken;

use DateTimeImmutable;
use packages\domain\model\authToken\AccessToken;
use packages\domain\model\authToken\AccessTokenExpiration;
use packages\domain\model\authToken\AuthToken;
use packages\domain\model\authToken\RefreshToken;

class TestAuthTokenFactory
{
    public static function create(
        ?AccessToken $accessToken = null,
        ?RefreshToken $refreshToken = null
    ): AuthToken
    {
        return new AuthToken(
            $accessToken ?? new AccessToken('test_access_token', new AccessTokenExpiration(new DateTimeImmutable('+30 minutes'))),
            $refreshToken ?? new RefreshToken('test_refresh_token')
        );
    }
}