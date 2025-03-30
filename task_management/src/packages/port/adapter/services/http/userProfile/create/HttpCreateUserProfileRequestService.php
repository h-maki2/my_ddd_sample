<?php

namespace packages\port\adapter\services\http\userProfile\create;

use Illuminate\Support\Facades\Http;
use packages\application\userProfile\CreateUserProfileRequestService;
use packages\application\userProfile\CreateUserProfileResponseData;
use packages\domain\model\auth\AuthenticationException;
use packages\domain\model\authToken\AccessToken;
use packages\port\adapter\services\common\identityAccessApi\IdentityAccessApiAcceptCreator;
use packages\port\adapter\services\common\identityAccessApi\IdentityAccessApiException;
use packages\port\adapter\services\common\identityAccessApi\IdentityAccessApiResponse;

class HttpCreateUserProfileRequestService implements CreateUserProfileRequestService
{
    private const URL_TEMPLATE = 'create';

    public function send(
        AccessToken $accessToken,
        string $name,
        string $selfIntroductionText
    ): CreateUserProfileResponseData
    {
        $response = Http::asForm()
                ->withHeaders([
                    'Authorization' => $accessToken->headerValue(),
                    'Accept' => IdentityAccessApiAcceptCreator::create('v1'),
                ])
                ->post($this->buildUrl(), [
                    'name' => $name,
                    'selfIntroductionText' => $selfIntroductionText
                ]);

        
        if ($response->status() >= 500) {
            throw new IdentityAccessApiException(
                'Identity Access API error: ' . print_r($response->json(), true)
            );
        }

        if ($response->status() === 401) {
            throw new AuthenticationException(
                'Authentication error: ' . print_r($response->json(), true)
            );
        }

        if ($response->status() >= 400) {
            $apiResponseData = new IdentityAccessApiResponse($response);
            return new CreateUserProfileResponseData(
                false,
                $apiResponseData->errorResponse(),
            );
        }

        return new CreateUserProfileResponseData(
            true,
            []
        );
    }

    private function buildUrl(): string
    {
        return config('app.identity_access_container_uri') . self::URL_TEMPLATE;
    }
}