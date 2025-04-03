<?php

namespace packages\port\adapter\services\http\userAccount;

use packages\domain\model\auth\Scope;
use packages\domain\model\authToken\AccessToken;
use packages\domain\model\userProfile\userAccount\IUserAccountService;
use packages\domain\model\userProfile\userAccount\UserAccountData;

class HttpUserAccountService implements IUserAccountService
{
    public function userAccountFrom(AccessToken $accessToken, Scope $scope): UserAccountData
    {
        return (new HttpUserAccountAdapter())->toUserAccountData($accessToken, $scope);
    }
}