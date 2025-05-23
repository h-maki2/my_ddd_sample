<?php

namespace packages\test\helpers\domain\userProfile;

use packages\domain\model\authenticationAccount\UserId;
use packages\domain\model\userProfile\SelfIntroductionText;
use packages\domain\model\userProfile\UserName;
use packages\domain\model\userProfile\UserProfile;
use packages\domain\model\userProfile\UserProfileId;
use Ramsey\Uuid\Uuid;

class TestUserProfileFactory
{
    public static function create(
        UserId $userId,
        ?UserName $userName = null,
        ?SelfIntroductionText $selfIntroductionText = null
    ): UserProfile
    {
        return UserProfile::reconstruct(
            $userId,
            $userName ?? new UserName('test-user-name'),
            $selfIntroductionText ?? new SelfIntroductionText('test-self-introduction-text')
        );
    }
}