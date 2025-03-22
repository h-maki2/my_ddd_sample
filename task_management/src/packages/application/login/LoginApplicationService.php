<?php

namespace packages\application\login;

use CreateAuthorizationRequestUrlResult;
use packages\domain\model\auth\IAuthCodeFetcher;
use packages\domain\model\auth\IAuthorizationRequestUrlBuildService;
use packages\domain\service\auth\AOneTimeTokenSessionService;
use packages\domain\service\auth\AuthorizationRequestUrlBuilder;

class LoginApplicationService
{
    private AOneTimeTokenSessionService $oneTimeTokenSessionService;
    private IAuthCodeFetcher $authCodeFetcher;
    private IAuthorizationRequestUrlBuildService $authorizationRequestUrlBuildService;

    public function __construct(
        AOneTimeTokenSessionService $oneTimeTokenSessionService,
        IAuthCodeFetcher $authCodeFetcher,
        IAuthorizationRequestUrlBuildService $authorizationRequestUrlBuildService
    )
    {
        $this->oneTimeTokenSessionService = $oneTimeTokenSessionService;
        $this->authCodeFetcher = $authCodeFetcher;
        $this->authorizationRequestUrlBuildService = $authorizationRequestUrlBuildService;
    }

    /**
     * 認可リクエスト用のURLを生成
     */
    public function createAuthorizationRequestUrl(
        string $email,
        string $password
    ): CreateAuthorizationRequestUrlResult
    {
        $authRequestUrlBuilder = new AuthorizationRequestUrlBuilder(
            $this->oneTimeTokenSessionService,
            $this->authorizationRequestUrlBuildService
        );

        try {
            $authRequestUrl = $authRequestUrlBuilder->build($email, $password);
        } catch (AccountLockedException $e) {
            // アカウントがロックされている場合
            return CreateAuthorizationRequestUrlResult::createWhenFailure(
                accountLocked: true
            );
        }

        if ($authRequestUrl === null) {
            return CreateAuthorizationRequestUrlResult::createWhenFailure(
                accountLocked: false
            );
        }

        return CreateAuthorizationRequestUrlResult::createWhenSuccess($authRequestUrl);
    }
}