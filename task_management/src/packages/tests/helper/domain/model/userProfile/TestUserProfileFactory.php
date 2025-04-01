<?php

namespace packages\test\helpers\domain\userProfile;

use packages\domain\model\userProfile\SelfIntroductionText;
use packages\domain\model\userProfile\userAccount\UserEmail;
use packages\domain\model\userProfile\userAccount\UserId;
use packages\domain\model\userProfile\UserName;
use packages\domain\model\userProfile\UserProfile;
use packages\domain\model\userProfile\UserProfileId;
use packages\test\helpers\domain\userProfile\userAccount\TestUserIdFactory;
use Ramsey\Uuid\Uuid;

class TestUserProfileFactory
{
    public static function create(
        ?UserId $userId = null,
        ?UserName $userName = null,
        ?SelfIntroductionText $selfIntroductionText = null,
        ?UserEmail $userEmail = null
    ): UserProfile
    {
        return UserProfile::reconstruct(
            $userId ?? TestUserIdFactory::create(),
            $userName ?? new UserName('test-user-name'),
            $selfIntroductionText ?? new SelfIntroductionText('test-self-introduction-text'),
            $userEmail ?? new UserEmail('test@test.com')
        );
    }
}