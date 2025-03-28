<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use packages\domain\model\auth\AOneTimeTokenSessionService;
use packages\domain\model\auth\IAuthorizationRequestUrlBuildService;
use packages\domain\model\auth\LoginUrlCreator;
use packages\port\adapter\services\authorizationRequestUrl\HttpAuthorizationRequestUrlBuildService;
use packages\port\adapter\services\oauth\OauthLoginUrlCreator;
use packages\port\adapter\services\oauth\OneTimeTokenSessionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            AOneTimeTokenSessionService::class,
            OneTimeTokenSessionService::class
        );

        $this->app->bind(
            LoginUrlCreator::class,
            OauthLoginUrlCreator::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
