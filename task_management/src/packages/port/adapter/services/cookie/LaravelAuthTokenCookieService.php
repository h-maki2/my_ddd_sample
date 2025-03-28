<?php

namespace packages\port\adapter\services\cookie;

use Illuminate\Support\Facades\Cookie;
use packages\domain\model\authToken\AAuthTokenCookieService;
use packages\domain\model\authToken\AccessToken;
use packages\domain\model\authToken\AuthToken;
use packages\domain\model\authToken\RefreshToken;

class LaravelAuthTokenCookieService extends AAuthTokenCookieService
{
    public function save(AuthToken $authToken): void
    {
        Cookie::queue(self::ACCESS_TOKEN_KEY_NAME, $authToken->accessToken(), self::TOKEN_EXPIRATION_KEY_NAME);
        Cookie::queue(self::REFRESH_TOKEN_KEY_NAME, $authToken->refreshToken(), self::TOKEN_EXPIRATION_KEY_NAME);
        Cookie::queue(self::TOKEN_EXPIRATION_KEY_NAME, $authToken->accessTokenExpiresIn(), self::COOKIE_EXPIRATION_MINUTES);
    }

    public function get(): ?AuthToken
    {
        $accessToken = Cookie::get(self::ACCESS_TOKEN_KEY_NAME);
        $refreshToken = Cookie::get(self::REFRESH_TOKEN_KEY_NAME);
        $tokenExpiration = Cookie::get(self::TOKEN_EXPIRATION_KEY_NAME);

        if ($accessToken === null) {
            return null;
        }

        return new AuthToken(
            new AccessToken($accessToken, (int)$tokenExpiration),
            new RefreshToken($refreshToken)
        );
    }

    public function clear(): void
    {
        Cookie::queue(Cookie::forget(self::ACCESS_TOKEN_KEY_NAME));
        Cookie::queue(Cookie::forget(self::REFRESH_TOKEN_KEY_NAME));
        Cookie::queue(Cookie::forget(self::TOKEN_EXPIRATION_KEY_NAME));
    }
}