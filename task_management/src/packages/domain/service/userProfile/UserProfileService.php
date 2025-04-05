<?php

namespace packages\domain\service\userProfile;

use packages\domain\model\userProfile\userAccount\UserId;
use packages\domain\model\userProfile\IUserProfileRepository;
use packages\domain\model\userProfile\UserName;

class UserProfileService
{
    private IUserProfileRepository $userProfileRepository;

    public function __construct(IUserProfileRepository $userProfileRepository)
    {
        $this->userProfileRepository = $userProfileRepository;
    }

    /**
     * 既に登録されているプロフィールかどうかを判定する
     */
    public function isExists(UserId $userId): bool
    {
        return $this->userProfileRepository->findById($userId) !== null;
    }
}