<?php

namespace packages\infrastructure\services\AuthorizationRequestUrlBuilder;

use packages\domain\model\auth\IAuthorizationRequestUrlBuilder;
use packages\domain\model\auth\IAuthorizationRequestUrlBuilderFetcher;

class HttpAuthorizationRequestUrlBuilder implements IAuthorizationRequestUrlBuilder
{
    public function build(
        string $email,
        string $password,
        string $oneTimeToken
    ): string {

        $httpAuthorizationRequestUrlBuilderAdapter = new HttpAuthorizationRequestUrlAdapter();
        return $httpAuthorizationRequestUrlBuilderAdapter->authorizationRequestUrlFrom($email, $password, $oneTimeToken);
    }
}