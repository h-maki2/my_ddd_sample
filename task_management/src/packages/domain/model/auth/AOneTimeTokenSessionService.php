<?php

namespace packages\domain\model\auth;

use packages\domain\model\auth\OneTimeToken;

abstract class AOneTimeTokenSessionService
{
    protected const ONE_TIME_TOKEN_SESSION_KEY = 'one_time_token_key';

    abstract public function save(OneTimeToken $oneTimeToken): void;

    abstract public function get(): ?OneTimeToken;

    abstract public function clear(): void;
}