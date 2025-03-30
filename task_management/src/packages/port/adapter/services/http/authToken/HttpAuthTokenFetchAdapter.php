<?php

namespace packages\port\adapter\services\http\authToken;

use DateTimeImmutable;
use Illuminate\Support\Facades\Http;
use packages\domain\model\auth\AuthenticationException;
use packages\domain\model\authToken\AccessToken;
use packages\domain\model\authToken\AccessTokenExpiration;
use packages\domain\model\authToken\AuthToken;
use packages\domain\model\authToken\RefreshToken;

class HttpAuthTokenFetchAdapter
{
    private const URL_TEMPLATE = 'oauth/token';
    private const GRANT_TYPE = 'authorization_code';

    public function toAuthToken(string $authCode): AuthToken
    {
        $result = $this->sendRequest($authCode);
        print_r($result);

        return $this->arrayToAuthToken($result);
    }

    private function sendRequest(string $authCode): array
    {
        $response = Http::asForm()->post($this->buildUrl(), [
            'grant_type'    => self::GRANT_TYPE,
            'client_id'     => config('app.client_id'),
            'client_secret' => config('app.client_secret'),
            'redirect_uri'  => config('app.redirect_url'),
            'code'          => $authCode,
        ]);

        if ($response->status() >= 400) {
            throw new AuthenticationException(print_r($response->json(), true));
        }

        return $response->json();
    }

    private function buildUrl(): string
    {
        return config('app.identity_access_container_uri') . self::URL_TEMPLATE;
    }

    private function arrayToAuthToken(array $result): AuthToken
    {
        return new AuthToken(
            new AccessToken(
                $result['access_token'],
                AccessTokenExpiration::create((int)$result['expires_in'])
            ),
            new RefreshToken(
                $result['refresh_token']
            )
        );
    }
}