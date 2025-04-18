<?php

namespace packages\domain\model\userProfile\userAccount;

class UserAccountData
{
    readonly UserId $userId;
    readonly UserEmail $userEmail;

    public function __construct(UserId $userId, UserEmail $userEmail)
    {
        $this->userId = $userId;
        $this->userEmail = $userEmail;
    }
}