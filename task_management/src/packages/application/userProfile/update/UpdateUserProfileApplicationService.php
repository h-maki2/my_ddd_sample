<?php

namespace packages\application\userProfile\update;

use packages\domain\model\authToken\AccessTokenFetcher;
use packages\domain\model\userProfile\IUserProfileRepository;
use packages\domain\model\userProfile\userAccount\IUserAccountService;
use packages\domain\service\userProfile\UserProfileService;

class UpdateUserProfileApplicationService
{
    private IUserProfileRepository $userProfileRepository;
    private UserProfileService $userProfileService;
    private AccessTokenFetcher $accessTokenFetcher;
    private IUserAccountService $userAccountService;

    public function __construct(
        IUserProfileRepository $userProfileRepository,
        AccessTokenFetcher $accessTokenFetcher,
        IUserAccountService $userAccountService
    ) {
        $this->userProfileRepository = $userProfileRepository;
        $this->userProfileService = new UserProfileService($userProfileRepository);
        $this->accessTokenFetcher = $accessTokenFetcher;
        $this->userAccountService = $userAccountService;
    }
}