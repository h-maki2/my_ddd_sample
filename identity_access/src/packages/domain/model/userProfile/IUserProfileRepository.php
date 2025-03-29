<?php

namespace packages\domain\model\userProfile;

use packages\domain\model\authenticationAccount\UserId;

interface IUserProfileRepository
{
    public function findById(UserId $userId): ?UserProfile;

    public function save(UserProfile $userProfile): void;
}