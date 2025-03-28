<?php

namespace packages\port\adapter\services\oauth;

use packages\domain\model\auth\LoginUrlCreator;

class OauthLoginUrlCreator extends LoginUrlCreator
{
    protected function clientId(): string
    {
        return config('app.client_id');
    }

    protected function redirectUrl(): string
    {
        return config('app.redirect_url');
    }

    protected function baseLoginUrl(): string
    {
        return config('app.identity_access_uri') . 'login';
    }
}