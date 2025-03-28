<?php

namespace packages\port\adapter\services\http\authToken;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use packages\domain\model\auth\AuthenticationException;
use packages\domain\model\authToken\AccessToken;
use packages\domain\model\authToken\AuthToken;
use packages\domain\model\authToken\RefreshToken;

class HttpRefreshAuthTokenAdapter
{
    private const URL_TEMPLATE = 'oauth/token';
    private const GRANT_TYPE = 'refresh_token';

    public function toAuthToken(RefreshToken $refreshToken): ?AuthToken
    {
        $result = $this->sendRequest($refreshToken);

        if ($result === null) {
            return null;
        }

        return $this->arrayToAuthToken($result);
    }

    private function sendRequest(RefreshToken $refreshToken): ?array
    {
        $response = Http::asForm()->post($this->buildUrl(), [
            'grant_type'    => self::GRANT_TYPE,
            'client_id'     => config('app.client_id'),
            'client_secret' => config('app.client_secret'),
            'refresh_token' => $refreshToken->value,
        ]);

        if ($response->status() >= 400) {
            Log::error(print_r($response->json(), true));
            return null;
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
                (int)$result['expires_in']
            ),
            new RefreshToken(
                $result['refresh_token']
            )
        );
    }
}