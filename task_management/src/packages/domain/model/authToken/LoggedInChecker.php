<?php

namespace packages\domain\model\authToken;

class LoggedInChecker
{
    private AAuthTokenCookieService $authTokenCookieService;
    private IAuthTokenService $authTokenService;

    public function __construct(
        AAuthTokenCookieService $authTokenCookieService,
        IAuthTokenService $authTokenService
    )
    {
        $this->authTokenCookieService = $authTokenCookieService;
        $this->authTokenService = $authTokenService;
    }

    /**
     * ログイン済みかどうかを判定
     */
    public function check(): bool
    {
        $authToken = $this->authTokenCookieService->get();
        if ($authToken === null) {
            return false;
        }

        if (!$authToken->accessTokenIsExpired()) {
            return true;
        }

        $authToken = $this->authTokenService->refreshAuthToken(
            $authToken->refreshToken
        );
        if ($authToken === null) {
            return false;
        }

        $this->authTokenCookieService->save($authToken);

        return true;
    }
}