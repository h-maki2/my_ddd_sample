<?php

namespace packages\port\adapter\services\AuthorizationRequestUrlBuildService;

use packages\domain\model\auth\IAuthorizationRequestUrlBuildService;

class HttpAuthorizationRequestUrlBuildService implements IAuthorizationRequestUrlBuildService
{
    /**
     * @throws AccountLockedException
     */
    public function build(
        string $email,
        string $password,
        string $oneTimeToken
    ): ?string 
    {

        $httpAuthorizationRequestUrlBuildServiceAdapter = new HttpAuthorizationRequestUrlBuildAdapter();
        return $httpAuthorizationRequestUrlBuildServiceAdapter->authorizationRequestUrlFrom($email, $password, $oneTimeToken);
    }
}