<?php

namespace packages\application\userProfile;

use packages\domain\model\auth\AuthenticationException;
use packages\domain\model\authToken\AAuthTokenStore;
use packages\domain\model\authToken\LoggedInChecker;

/**
 * ユーザープロフィール作成アプリケーションサービス
 */
class CreateUserProfileApplicationService
{
    private CreateUserProfileRequestService $createUserProfileRequestService;
    private AAuthTokenStore $authTokenStore;
    private LoggedInChecker $loggedInChecker;

    public function __construct(
        CreateUserProfileRequestService $createUserProfileRequestService,
        AAuthTokenStore $authTokenStore,
        LoggedInChecker $loggedInChecker
    ) {
        $this->createUserProfileRequestService = $createUserProfileRequestService;
        $this->authTokenStore = $authTokenStore;
        $this->loggedInChecker = $loggedInChecker;
    }

    public function create(
        string $name,
        string $selfIntroductionText
    ): CreateUserProfileResult
    {
        $authToken = $this->authTokenStore->get();
        if (!$this->loggedInChecker->check($authToken)) {
            throw new AuthenticationException('ログインしていません。');
        }

        $result = $this->createUserProfileRequestService->send(
            $authToken->accessToken,
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