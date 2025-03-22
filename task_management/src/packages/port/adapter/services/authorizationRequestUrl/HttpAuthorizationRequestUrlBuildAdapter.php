<?php

namespace packages\port\adapter\services\authorizationRequestUrl;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use packages\adapter\presenter\common\json\HttpStatus;
use packages\application\login\AccountLockedException;
use packages\domain\model\auth\Scope;
use packages\port\adapter\services\common\identityAccessApi\IdentityAccessApiAcceptCreator;
use packages\port\adapter\services\common\identityAccessApi\IdentityAccessApiFaildException;
use packages\port\adapter\services\common\identityAccessApi\IdentityAccessApiResponse;

class HttpAuthorizationRequestUrlBuildAdapter
{
    private const URL_TEMPLATE = 'api/login';

    /**
     * @throws AccountLockedException
     */
    public function authorizationRequestUrlFrom(
        string $email,
        string $password,
        string $oneTimeToken
    ): ?string
    {
        $response = $this->sendRequest($email, $password, $oneTimeToken);

        if ($response->status() >= 500) {
            throw new IdentityAccessApiFaildException($response->json());
        }

        $identityAccessApiResponse = new IdentityAccessApiResponse($response);

        if ($response->status() >= 400) {
            $errorRespone = $identityAccessApiResponse->errorResponse();
            if (isset($errorRespone['accountLocked']) && $errorRespone['accountLocked']) {
                throw new AccountLockedException('アカウントがロックされています。');
            }

            return null;
        }

        return $identityAccessApiResponse->successResponse()['authorizationUrl'];
    }

    private function sendRequest(
        string $email,
        string $password,
        string $oneTimeToken
    ): Response
    {
        return Http::withHeaders([
            'Accept' => IdentityAccessApiAcceptCreator::create('v1'),
        ])->post($this->buildUrl(), [
            'email' => $email,
            'password' => $password,
            'client_id' => config('app.client_id'),
            'redirect_url' => config('app.redirect_url'),
            'response_type' => 'code',
            'state' => $oneTimeToken,
            'scope' => $this->scope(),
        ]);
    }

    private function buildUrl(): string
    {
        return config('app.identity_access_uri') . self::URL_TEMPLATE;
    }

    private function scope(): string
    {
        return Scope::ReadAccount->value . ' ' . Scope::EditAccount->value . ' ' . Scope::DeleteAccount->value;
    }
}