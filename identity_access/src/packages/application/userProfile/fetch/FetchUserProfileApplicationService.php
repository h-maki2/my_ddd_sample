<?php

namespace packages\application\userProfile\fetch;

use packages\application\userProfile\fetch\fetchLoggedInUserProfile\FetchLoggedInUserProfileResult;
use packages\domain\model\authenticationAccount\IAuthenticationAccountRepository;
use packages\domain\model\oauth\scope\IScopeAuthorizationChecker;
use packages\domain\model\oauth\scope\Scope;
use packages\domain\model\userProfile\IUserProfileRepository;
use packages\domain\service\authenticationAccount\AuthenticationService;
use packages\domain\service\oauth\LoggedInUserIdFetcher;
use RuntimeException;

class FetchUserProfileApplicationService
{
    private IUserProfileRepository $userProfileRepository;
    private LoggedInUserIdFetcher $loggedInUserIdFetcher;
    private IAuthenticationAccountRepository $authenticationAccountRepository;

    public function __construct(
        IUserProfileRepository $userProfileRepository,
        AuthenticationService $authService,
        IScopeAuthorizationChecker $scopeAuthorizationChecker,
        IAuthenticationAccountRepository $authenticationAccountRepository
    )
    {
        $this->userProfileRepository = $userProfileRepository;
        $this->loggedInUserIdFetcher = new LoggedInUserIdFetcher($authService, $scopeAuthorizationChecker);
        $this->authenticationAccountRepository = $authenticationAccountRepository;
    }

    public function fetchLoggedInUserProfile(string $scope): FetchLoggedInUserProfileResult
    {
        $userId = $this->loggedInUserIdFetcher->fetch(Scope::from($scope));

        $authAccount = $this->authenticationAccountRepository->findById($userId);
        
        $userProfile = $this->userProfileRepository->findById($userId);
        if ($userProfile === null) {
            return FetchLoggedInUserProfileResult::createWhenNotFound();
        }

        return FetchLoggedInUserProfileResult::createWhenFound(
            $userProfile->userId()->value,
            $userProfile->name()->value,
            $userProfile->selfIntroductionText()->value,
            $authAccount->email()->value
        );
    }
}