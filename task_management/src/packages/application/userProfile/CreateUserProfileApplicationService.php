<?php

namespace packages\application\userProfile;

use packages\domain\model\auth\AuthenticationException;
use packages\domain\model\authToken\AAuthTokenStore;
use packages\domain\model\authToken\AccessTokenFetcher;
use packages\domain\model\authToken\IAuthTokenService;
use packages\domain\model\authToken\LoggedInChecker;

/**
 * ユーザープロフィール作成アプリケーションサービス
 */
class CreateUserProfileApplicationService
{
    private CreateUserProfileRequestService $createUserProfileRequestService;
    private LoggedInChecker $loggedInChecker;
    private AccessTokenFetcher $accessTokenFetcher;

    public function __construct(
        CreateUserProfileRequestService $createUserProfileRequestService,
        AAuthTokenStore $authTokenStore,
        IAuthTokenService $authTokenService
    ) {
        $this->createUserProfileRequestService = $createUserProfileRequestService;
        $this->loggedInChecker = new LoggedInChecker($authTokenStore, $authTokenService);
        $this->accessTokenFetcher = new AccessTokenFetcher($authTokenStore, $authTokenService);
    }

    public function create(
        string $name,
        string $selfIntroductionText
    ): CreateUserProfileResult
    {
        $accessToken = $this->accessTokenFetcher->fetch();

        $result = $this->createUserProfileRequestService->send(
            $accessToken,
            $name,
            $selfIntroductionText
        );

        if (!$result->isSuccess) {
            return CreateUserProfileResult::createWhenFailure(
                $result->validationErrorMessageList
            );
        }

        return CreateUserProfileResult::createWhenSuccess();
    }
}