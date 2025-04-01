<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use packages\application\userProfile\CreateUserProfileRequestService;
use packages\domain\model\auth\AOneTimeTokenSessionService;
use packages\domain\model\auth\IAuthorizationRequestUrlBuildService;
use packages\domain\model\auth\LoginUrlCreator;
use packages\domain\model\authToken\AAuthTokenStore;
use packages\domain\model\authToken\IAuthTokenService;
use packages\port\adapter\services\authorizationRequestUrl\HttpAuthorizationRequestUrlBuildService;
use packages\port\adapter\services\cookie\CookieAuthTokenStore;
use packages\port\adapter\services\http\authToken\HttpAuthTokenService;
use packages\port\adapter\services\http\userProfile\create\HttpCreateUserProfileRequestService;
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

        $this->app->bind(
            IAuthTokenService::class,
            HttpAuthTokenService::class
        );

        $this->app->bind(
            AAuthTokenStore::class,
            CookieAuthTokenStore::class
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
