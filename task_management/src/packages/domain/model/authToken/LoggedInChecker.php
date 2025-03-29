<?php

namespace packages\domain\model\authToken;

class LoggedInChecker
{
    private AAuthTokenStore $authTokenStore;
    private IAuthTokenService $authTokenService;

    public function __construct(AAuthTokenStore $authTokenStore, IAuthTokenService $authTokenService)
    {
        $this->authTokenStore = $authTokenStore;
        $this->authTokenService = $authTokenService;
    }

    /**
     * ログイン済みかどうかを判定
     */
    public function check(?AuthToken $authToken): bool
    {
        if ($authToken === null) {
            return false;
        }

        if (!$authToken->accessTokenIsExpired()) {
            return true;
        }

        $authToken = $this->authTokenService->refreshAuthToken($authToken->refreshToken);
        if ($authToken === null) {
            return false;
        }
        $this->authTokenStore->save($authToken);

        return true;
    }
}