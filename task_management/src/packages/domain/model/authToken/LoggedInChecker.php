<?php

namespace packages\domain\model\authToken;

class LoggedInChecker
{
    /**
     * ログイン済みかどうかを判定
     */
    public function check(?AuthToken $authToken): bool
    {
        if ($authToken === null) {
            return false;
        }

        if ($authToken->accessTokenIsExpired()) {
            return false;
        }

        return true;
    }
}