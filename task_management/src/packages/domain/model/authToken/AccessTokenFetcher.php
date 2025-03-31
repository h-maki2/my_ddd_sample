<?php

namespace packages\domain\model\authToken;

use packages\domain\model\auth\AuthenticationException;

class AccessTokenFetcher
{
    private AAuthTokenStore $authTokenStore;
    private LoggedInChecker $loggedInChecker;

    public function __construct(
        AAuthTokenStore $authTokenStore,
        IAuthTokenService $authTokenService
    )
    {
        $this->authTokenStore = $authTokenStore;
        $this->loggedInChecker = new LoggedInChecker($authTokenStore, $authTokenService);
    }

    public function fetch(): AccessToken
    {
        if (!$this->loggedInChecker->check()) {
            throw new AuthenticationException('ログインしていません');
        }

        $authToken = $this->authTokenStore->get();
        return $authToken->accessToken;
    }
}