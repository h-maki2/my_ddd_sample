<?php

namespace packages\tests\helper\domain\model\userProfile\userAccount;

use packages\domain\model\userProfile\userAccount\UserAccountData;
use packages\domain\model\userProfile\userAccount\UserEmail;
use packages\domain\model\userProfile\userAccount\UserId;

class TestUserAccountFactory
{
    public static function create(
        ?UserId $userId = null,
        ?UserEmail $userEmail = null,
    ): UserAccountData
    {
        return new UserAccountData(
            $userId ?? TestUserIdFactory::create(),
            $userEmail ?? new UserEmail('test@test.com')
        );
    }
}