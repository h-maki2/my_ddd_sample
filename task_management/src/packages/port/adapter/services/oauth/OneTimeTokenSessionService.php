<?php

namespace packages\port\adapter\services\oauth;

use packages\domain\model\auth\AOneTimeTokenSessionService;
use packages\domain\model\auth\OneTimeToken;

class OneTimeTokenSessionService extends AOneTimeTokenSessionService
{
    public function save(OneTimeToken $oneTimeToken): void
    {
        session([self::ONE_TIME_TOKEN_SESSION_KEY => $oneTimeToken]);
    }

    public function get(): ?OneTimeToken
    {
        $oneTimeTokenString = session(self::ONE_TIME_TOKEN_SESSION_KEY);
        if ($oneTimeTokenString === null) {
            return null;
        }
        return session(self::ONE_TIME_TOKEN_SESSION_KEY);
    }


    public function clear(): void
    {
        session()->forget(self::ONE_TIME_TOKEN_SESSION_KEY);
    }
}