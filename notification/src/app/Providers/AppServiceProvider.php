<?php

namespace App\Providers;

use dddCommonLib\domain\model\common\IMessagingLogger;
use Illuminate\Support\ServiceProvider;
use packages\adapter\email\LaravelEmailSender;
use packages\adapter\messaging\kafka\LaravelMessagingLogger;
use packages\domain\model\email\IEmailSender;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IMessagingLogger::class, LaravelMessagingLogger::class);
        $this->app->bind(IEmailSender::class, LaravelEmailSender::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
