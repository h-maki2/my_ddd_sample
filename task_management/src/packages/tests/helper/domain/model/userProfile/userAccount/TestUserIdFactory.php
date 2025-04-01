<?php

namespace packages\test\helpers\domain\userProfile\userAccount;

use packages\domain\model\userProfile\userAccount\UserId;

class TestUserIdFactory
{
    public static function create(): UserId
    {
        return new UserId('0188b2a6-bd94-7ccf-9666-1df7e26ac6b8');
    }
}