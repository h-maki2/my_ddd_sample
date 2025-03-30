<?php

use App\Http\Controllers\authentication\AuthenticationControllers;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
use packages\port\adapter\services\cookie\CookieAuthTokenStore;

Route::get('/', function () {
    $store = new CookieAuthTokenStore();
    print_r($store->get());
});

Route::get('/login', [AuthenticationControllers::class, 'displayLoginPage']);
Route::get('/auth/callback', [AuthenticationControllers::class, 'login']);

Route::get('/test', [TestController::class, 'index']);