<?php

namespace packages\application\login;

use packages\domain\model\auth\AOneTimeTokenSessionService;
use packages\domain\model\auth\AuthenticationException;
use packages\domain\model\auth\IAuthCodeFetcher;
use packages\domain\model\auth\IAuthorizationRequestUrlBuildService;
use packages\domain\model\auth\OneTimeToken;
use packages\domain\model\auth\AuthorizationRequestUrlBuilder;
use packages\domain\model\auth\LoginUrlCreator;
use packages\domain\model\authToken\AAuthTokenStore;
use packages\domain\model\authToken\IAuthTokenService;

class LoginApplicationService
{
    private AOneTimeTokenSessionService $oneTimeTokenSessionService;
    private LoginUrlCreator $loginUrlCreator;
    private IAuthTokenService $authTokenService;
    private AAuthTokenStore $authTokenStore;

    public function __construct(
        AOneTimeTokenSessionService $oneTimeTokenSessionService,
        LoginUrlCreator $loginUrlCreator,
        IAuthTokenService $authTokenService,
        AAuthTokenStore $authTokenStore
    )
    {
        $this->oneTimeTokenSessionService = $oneTimeTokenSessionService;
        $this->loginUrlCreator = $loginUrlCreator;
        $this->authTokenService = $authTokenService;
        $this->authTokenStore = $authTokenStore;
    }

    public function createLoginUrl(): string
    {
        return $this->loginUrlCreator->createLoginUrl();
    }

    public function login(string $authCode, string $oneTimeToken)
    {
        $oneTimeToken = OneTimeToken::reconstruct($oneTimeToken);
        $otherOneTimeToken = $this->oneTimeTokenSessionService->get();
        if (!$oneTimeToken->equals($otherOneTimeToken)) {
            throw new AuthenticationException('oneTimeTokenが一致しません。');
        }

        $authToken = $this->authTokenService->fetchByAuthCode($authCode);
        if ($authToken === null) {
            throw new AuthenticationException('アクセストークンととフレッシュトークンの取得に失敗しました。');
        }
        $this->authTokenStore->save($authToken);
    }
}