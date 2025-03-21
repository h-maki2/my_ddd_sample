<?php

namespace packages\infrastructure\services\AuthorizationRequestUrlBuilder;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use packages\adapter\presenter\common\json\HttpStatus;
use packages\domain\model\auth\Scope;
use packages\infrastructure\services\common\identityAccessApi\IdentityAccessApiAcceptCreator;
use packages\infrastructure\services\common\identityAccessApi\IdentityAccessApiFaildException;
use packages\infrastructure\services\common\identityAccessApi\IdentityAccessApiResponse;

class HttpAuthorizationRequestUrlAdapter
{
    private const URL_TEMPLATE = 'api/login';

    public function authorizationRequestUrlFrom(
        string $email,
        string $password,
        string $oneTimeToken
    ): ?string
    {
        $response = $this->sendRequest($email, $password, $oneTimeToken);

        if ($response->status() === HttpStatus::InternalServerError->value) {
            throw new IdentityAccessApiFaildException($response->json());
        }

        $identityAccessApiResponse = new IdentityAccessApiResponse($response);

        if ($response->status() === HttpStatus::Unauthorized->value) {
            $accountRock = $identityAccessApiResponse->errorResponse()['accountLocked'];
            if ($accountRock) {
                throw new AccountRockException('アカウントがロックされています。');
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
            'client_id' => app('app.client_id'),
            'redirect_url' => app('app.redirect_url'),
            'response_type' => 'code',
            'state' => $oneTimeToken,
            'scope' => $this->scope(),
        ]);
    }

    private function buildUrl(): string
    {
        return app('app.identity_access_uri') . self::URL_TEMPLATE;
    }

    private function scope(): string
    {
        return Scope::ReadAccount->value . ' ' . Scope::EditAccount->value . ' ' . Scope::DeleteAccount->value;
    }
}