<?php

namespace packages\application\login;

use packages\domain\model\auth\AOneTimeTokenSessionService;
use packages\domain\model\auth\IAuthCodeFetcher;
use packages\domain\model\auth\IAuthorizationRequestUrlBuildService;
use packages\domain\service\auth\AuthorizationRequestUrlBuilder;

class LoginApplicationService
{
    private AOneTimeTokenSessionService $oneTimeTokenSessionService;
    private IAuthorizationRequestUrlBuildService $authorizationRequestUrlBuildService;

    public function __construct(
        AOneTimeTokenSessionService $oneTimeTokenSessionService,
        IAuthorizationRequestUrlBuildService $authorizationRequestUrlBuildService
    )
    {
        $this->oneTimeTokenSessionService = $oneTimeTokenSessionService;
        $this->authorizationRequestUrlBuildService = $authorizationRequestUrlBuildService;
    }

    /**
     * ログインする
     */
    public function login(
        string $email,
        string $password
    ): LoginResult
    {
        $authRequestUrlBuilder = new AuthorizationRequestUrlBuilder(
            $this->oneTimeTokenSessionService,
            $this->authorizationRequestUrlBuildService
        );

        try {
            $authRequestUrl = $authRequestUrlBuilder->build($email, $password);
        } catch (AccountLockedException $e) {
            // アカウントがロックされている場合
            return LoginResult::createWhenFailure(
                accountLocked: true
            );
        }

        if ($authRequestUrl === null) {
            return LoginResult::createWhenFailure(
                accountLocked: false
            );
        }

        return LoginResult::createWhenSuccess($authRequestUrl);
    }
}