<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use packages\domain\model\auth\AOneTimeTokenSessionService;
use packages\domain\model\auth\IAuthorizationRequestUrlBuildService;
use packages\port\adapter\services\authorizationRequestUrl\HttpAuthorizationRequestUrlBuildService;
use packages\port\adapter\services\laravel\LaravelOneTimeTokenSessionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            AOneTimeTokenSessionService::class,
            LaravelOneTimeTokenSessionService::class
        );

        $this->app->bind(
            IAuthorizationRequestUrlBuildService::class,
            HttpAuthorizationRequestUrlBuildService::class
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
