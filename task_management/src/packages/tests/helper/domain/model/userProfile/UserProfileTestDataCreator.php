<?php

namespace packages\tests\helper\domain\model\userProfile;

use packages\domain\model\userProfile\IUserProfileRepository;
use packages\domain\model\userProfile\SelfIntroductionText;
use packages\domain\model\userProfile\userAccount\UserEmail;
use packages\domain\model\userProfile\userAccount\UserId;
use packages\domain\model\userProfile\UserName;
use packages\domain\model\userProfile\UserProfile;

class UserProfileTestDataCreator
{
    private IUserProfileRepository $userProfileRepository;

    public function __construct(
        IUserProfileRepository $userProfileRepository
    )
    {
        $this->userProfileRepository = $userProfileRepository;
    }

    public function create(
        ?UserId $userId,
        ?UserEmail $userEmail = null,
        ?UserName $userName = null,
        ?SelfIntroductionText $selfIntroductionText = null
    ): UserProfile 
    {
        $userProfile = TestUserProfileFactory::create($userId, $userName, $selfIntroductionText, $userEmail);
        $this->userProfileRepository->save($userProfile);

        return $userProfile;
    }
}