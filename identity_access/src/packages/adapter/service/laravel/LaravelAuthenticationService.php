<?php

namespace packages\adapter\service\laravel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use packages\domain\model\authenticationAccount\UserId;
use packages\domain\service\authenticationAccount\AuthenticationService;

class LaravelAuthenticationService implements AuthenticationService
{
    public function markAsLoggedIn(UserId $userId): void
    {
        Auth::guard('web')->loginUsingId($userId->value);
        Log::info(Auth::id());
    }

    public function loggedInUserId(): ?UserId
    {
        if (Auth::check()) {
            return new UserId(Auth::id());
        }

        return null;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}