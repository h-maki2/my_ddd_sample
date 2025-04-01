<?php

namespace packages\domain\model\userProfile\userAccount;

use packages\domain\model\auth\Scope;
use packages\domain\model\authToken\AccessToken;

interface IUserAccountService
{
    public function userAccountFrom(AccessToken $accessToken, Scope $scope): UserAccountData;
}