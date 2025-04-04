<?php

namespace packages\application\userProfile\update;

use packages\domain\model\auth\AuthenticationException;
use packages\domain\model\auth\Scope;
use packages\domain\model\authToken\AAuthTokenStore;
use packages\domain\model\authToken\AccessTokenFetcher;
use packages\domain\model\authToken\IAuthTokenService;
use packages\domain\model\authToken\LoggedInChecker;
use packages\domain\model\common\validator\ValidationHandler;
use packages\domain\model\userProfile\IUserProfileRepository;
use packages\domain\model\userProfile\SelfIntroductionText;
use packages\domain\model\userProfile\userAccount\IUserAccountService;
use packages\domain\model\userProfile\UserName;
use packages\domain\model\userProfile\UserProfileNotExistsException;
use packages\domain\model\userProfile\validation\SelfIntroductionTextValidation;
use packages\domain\model\userProfile\validation\UserNameValidation;
use packages\domain\service\userProfile\UserProfileService;
use RuntimeException;

class UpdateUserProfileApplicationService
{
    private IUserProfileRepository $userProfileRepository;
    private AccessTokenFetcher $accessTokenFetcher;
    private IUserAccountService $userAccountService;
    private LoggedInChecker $loggedInChecker;

    public function __construct(
        IUserProfileRepository $userProfileRepository,
        AAuthTokenStore $authTokenStore,
        IAuthTokenService $authTokenService,
        IUserAccountService $userAccountService
    ) {
        $this->userProfileRepository = $userProfileRepository;
        $this->accessTokenFetcher = new AccessTokenFetcher($authTokenStore, $authTokenService);
        $this->loggedInChecker = new LoggedInChecker($authTokenStore, $authTokenService);
        $this->userAccountService = $userAccountService;
    }

    public function displayUpdateUserProfileForm(): DisplayUpdateUserProfileFormResult
    {
        if (!$this->loggedInChecker->check()) {
            throw new AuthenticationException('ログインしていません');
        }

        $accessToken = $this->accessTokenFetcher->fetch();

        $userAccount = $this->userAccountService->userAccountFrom($accessToken, Scope::ReadAccount);

        $userProfile = $this->userProfileRepository->findById($userAccount->userId);
        if ($userProfile === null) {
            throw new UserProfileNotExistsException('ユーザープロフィールが見つかりません');
        }

        return new DisplayUpdateUserProfileFormResult(
            $userProfile->name()->value,
            $userProfile->selfIntroductionText()->value
        );
    }

    public function update(
        string $userNameString,
        string $selfIntroductionTextString
    ): UpdateUserProfileResult
    {
        if (!$this->loggedInChecker->check()) {
            throw new AuthenticationException('ログインしていません');
        }

        $validationHandler = new ValidationHandler();
        $validationHandler->addValidator(new UserNameValidation($userNameString));
        $validationHandler->addValidator(new SelfIntroductionTextValidation($selfIntroductionTextString));
        if (!$validationHandler->validate()) {
            return UpdateUserProfileResult::createWhenFailure($validationHandler->errorMessages());
        }

        $accessToken = $this->accessTokenFetcher->fetch();
        $userAccount = $this->userAccountService->userAccountFrom($accessToken, Scope::ReadAccount);
        $userProfile = $this->userProfileRepository->findById($userAccount->userId);
        if ($userProfile === null) {
            throw new UserProfileNotExistsException('ユーザープロフィールが見つかりません');
        }

        $userProfile->changeName(new UserName($userNameString));
        $userProfile->changeSelfIntroductionText(new SelfIntroductionText($selfIntroductionTextString));
        $this->userProfileRepository->save($userProfile);

        return UpdateUserProfileResult::createWhenSuccess();
    }
}