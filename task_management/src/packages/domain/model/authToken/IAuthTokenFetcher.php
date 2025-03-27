<?php

namespace packages\domain\model\authToken;

interface IAuthTokenFetcher
{
    public function fetchByAuthCode(string $authCode): AuthToken;
}