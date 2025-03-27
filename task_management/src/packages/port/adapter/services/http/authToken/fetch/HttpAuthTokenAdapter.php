<?php

namespace packages\port\adapter\services\http\authToken\fetch;

use Illuminate\Support\Facades\Http;
use packages\domain\model\auth\AuthenticationException;

class HttpAuthTokenAdapter
{
    private const URL_TEMPLATE = 'oauth/token';
    private const GRANT_TYPE = 'authorization_code';

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
}