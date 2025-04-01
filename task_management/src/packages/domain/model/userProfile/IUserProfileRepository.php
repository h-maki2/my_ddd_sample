<?php

namespace packages\domain\model\userProfile;

use packages\domain\model\userProfile\userAccount\UserId;

interface IUserProfileRepository
{
    public function findById(UserId $userId): ?UserProfile;

    public function save(UserProfile $userProfile): void;
}