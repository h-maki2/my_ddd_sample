<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use packages\port\adapter\persistence\eloquent\EloquentUserProfileRepository;
use packages\application\userProfile\CreateUserProfileRequestService;
use packages\domain\model\auth\AOneTimeTokenSessionService;
use packages\domain\model\auth\IAuthorizationRequestUrlBuildService;
use packages\domain\model\auth\LoginUrlCreator;
use packages\domain\model\authToken\AAuthTokenStore;
use packages\domain\model\authToken\IAuthTokenService;
use packages\domain\model\userProfile\IUserProfileRepository;
use packages\domain\model\userProfile\userAccount\IUserAccountService;
use packages\port\adapter\services\authorizationRequestUrl\HttpAuthorizationRequestUrlBuildService;
use packages\port\adapter\services\cookie\CookieAuthTokenStore;
use packages\port\adapter\services\http\authToken\HttpAuthTokenService;
use packages\port\adapter\services\http\userAccount\HttpUserAccountService;
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

        $this->app->bind(
            IUserAccountService::class,
            HttpUserAccountService::class
        );

        $this->app->bind(
            IUserProfileRepository::class,
            EloquentUserProfileRepository::class
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
