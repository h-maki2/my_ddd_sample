<?php

namespace packages\port\adapter\services\http\authToken;

use packages\domain\model\authToken\AuthToken;
use packages\domain\model\authToken\IAuthTokenService;
use packages\domain\model\authToken\RefreshToken;

class HttpAuthTokenService implements IAuthTokenService
{
    public function fetchByAuthCode(string $authCode): AuthToken
    {
        $adapter = new HttpAuthTokenFetchAdapter();
        return $adapter->toAuthToken($authCode);
    }

    public function refreshAuthToken(RefreshToken $refreshToken): ?AuthToken
    {
        $adapter = new HttpRefreshAuthTokenAdapter();
        return $adapter->toAuthToken($refreshToken);
    }
}