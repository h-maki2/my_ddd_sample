<?php

namespace packages\port\adapter\services\http\userAccount;

use Illuminate\Support\Facades\Http;
use packages\domain\model\auth\Scope;
use packages\domain\model\authToken\AccessToken;
use packages\domain\model\userProfile\userAccount\UserAccountData;
use packages\domain\model\userProfile\userAccount\UserEmail;
use packages\domain\model\userProfile\userAccount\UserId;
use packages\port\adapter\services\common\identityAccessApi\IdentityAccessApiAcceptCreator;
use packages\port\adapter\services\common\identityAccessApi\IdentityAccessApiException;
use packages\port\adapter\services\common\identityAccessApi\IdentityAccessApiVersion;

class HttpUserAccountAdapter
{
    private const URL_TEMPLATE = 'api/account/get';

    public function toUserAccountData(AccessToken $accessToken, Scope $scope): UserAccountData
    {
        $apiResult = $this->sendRequest($accessToken, $scope);
        return new UserAccountData(
            new UserId($apiResult['userId']),
            new UserEmail($apiResult['email']),
        );
    }

    private function sendRequest(AccessToken $accessToken, Scope $scope): array
    {
        $response = Http::asForm()
            ->withHeaders([
                'Authorization' => $accessToken->headerValue(),
                'Accept' => IdentityAccessApiAcceptCreator::create(IdentityAccessApiVersion::V1)
            ])
            ->post($this->buildUrl(), [
                'scope' => $scope->value
            ]);

        if ($response->status() >= 400) {
            throw new IdentityAccessApiException(print_r($response->json(), true));
        }

        return $response->json();
    }

    private function buildUrl(): string
    {
        return config('app.identity_access_container_uri') . self::URL_TEMPLATE;
    }
}