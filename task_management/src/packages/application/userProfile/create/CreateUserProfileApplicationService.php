<?php

namespace packages\application\userProfile\create;

use Illuminate\Support\Facades\Log;
use packages\domain\model\authToken\AAuthTokenStore;
use packages\domain\model\authToken\AccessTokenFetcher;
use packages\domain\model\authToken\IAuthTokenService;
use packages\domain\model\common\exception\AuthenticationException;
use packages\domain\model\common\validator\ValidationHandler;
use packages\domain\model\oauth\scope\IScopeAuthorizationChecker;
use packages\domain\model\oauth\scope\Scope;
use packages\domain\model\userProfile\IUserProfileRepository;
use packages\domain\model\userProfile\SelfIntroductionText;
use packages\domain\model\userProfile\UserName;
use packages\domain\model\userProfile\UserProfile;
use packages\domain\model\userProfile\validation\SelfIntroductionTextValidation;
use packages\domain\model\userProfile\validation\UserNameValidation;
use packages\domain\service\authenticationAccount\AuthenticationService;
use packages\domain\service\oauth\LoggedInUserIdFetcher;
use packages\domain\service\userProfile\UserProfileService;
use RuntimeException;

class CreateUserProfileApplicationService
{
    private IUserProfileRepository $userProfileRepository;
    private UserProfileService $userProfileService;
    private AccessTokenFetcher $accessTokenFetcher;

    public function __construct(
        IUserProfileRepository $userProfileRepository,
        AuthenticationService $authService,
        AAuthTokenStore $authTokenStore,
        IAuthTokenService $authTokenService
    )
    {
        $this->userProfileRepository = $userProfileRepository;
        $this->userProfileService = new UserProfileService($userProfileRepository);
        $this->accessTokenFetcher = new AccessTokenFetcher($authTokenStore, $authTokenService);
    }

    /**
     * ユーザープロフィールを作成する
     */
    public function create(
        string $userNameString, 
        string $selfIntroductionTextString,
        string $scopeString
    ): CreateUserProfileResult
    {
        $accessToken = $this->accessTokenFetcher->fetch();

        $validationHandler = new ValidationHandler();
        $validationHandler->addValidator(new UserNameValidation($userNameString));
        $validationHandler->addValidator(new SelfIntroductionTextValidation($selfIntroductionTextString));
        if (!$validationHandler->validate()) {
            return CreateUserProfileResult::createWhenFailure($validationHandler->errorMessages());
        }

        $userName = New UserName($userNameString);
        $selfIntroductionText = new SelfIntroductionText($selfIntroductionTextString);

        $userProfile = UserProfile::create(
            $userId,
            $userName,
            $selfIntroductionText,
            $this->userProfileService
        );
        $this->userProfileRepository->save($userProfile);

        return CreateUserProfileResult::createWhenSuccess();
    }
}