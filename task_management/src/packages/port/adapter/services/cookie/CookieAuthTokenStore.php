<?php

namespace packages\port\adapter\services\cookie;

use DateTimeImmutable;
use Illuminate\Support\Facades\Cookie;
use packages\domain\model\authToken\AAuthTokenCookieService;
use packages\domain\model\authToken\AAuthTokenStore;
use packages\domain\model\authToken\AccessToken;
use packages\domain\model\authToken\AccessTokenExpiration;
use packages\domain\model\authToken\AuthToken;
use packages\domain\model\authToken\RefreshToken;

class CookieAuthTokenStore extends AAuthTokenStore
{
    public function save(AuthToken $authToken): void
    {
        Cookie::queue(self::ACCESS_TOKEN_KEY_NAME, $authToken->accessToken->value, self::COOKIE_EXPIRATION_MINUTES);
        Cookie::queue(self::REFRESH_TOKEN_KEY_NAME, $authToken->refreshToken->value, self::COOKIE_EXPIRATION_MINUTES);
        Cookie::queue(self::TOKEN_EXPIRATION_KEY_NAME, $authToken->accessToken->expiration(), self::COOKIE_EXPIRATION_MINUTES);
    }

    public function clear(): void
    {
        Cookie::queue(Cookie::forget(self::ACCESS_TOKEN_KEY_NAME));
        Cookie::queue(Cookie::forget(self::REFRESH_TOKEN_KEY_NAME));
        Cookie::queue(Cookie::forget(self::TOKEN_EXPIRATION_KEY_NAME));
    }

    public function get(): ?AuthToken
    {
        $accessToken = Cookie::get(self::ACCESS_TOKEN_KEY_NAME);
        $refreshToken = Cookie::get(self::REFRESH_TOKEN_KEY_NAME);
        $tokenExpiration = Cookie::get(self::TOKEN_EXPIRATION_KEY_NAME);

        if ($accessToken === null || $refreshToken === null || $tokenExpiration === null) {
            return null;
        }

        return new AuthToken(
            new AccessToken(
                $accessToken, 
                AccessTokenExpiration::reconstruct(
                    new DateTimeImmutable($tokenExpiration),
                )
            ),
            new RefreshToken($refreshToken)
        );
    }
}