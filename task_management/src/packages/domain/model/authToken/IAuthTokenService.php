<?php

namespace packages\domain\model\authToken;

interface IAuthTokenService
{
    public function fetchByAuthCode(string $authCode): AuthToken;

    public function refreshAuthToken(RefreshToken $refreshToken): ?AuthToken;
}