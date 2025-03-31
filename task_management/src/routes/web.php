<?php

use App\Http\Controllers\authentication\AuthenticationControllers;
use App\Http\Controllers\TestController;
use App\Http\Controllers\userProfile\CreateUserProfileController;
use Illuminate\Support\Facades\Route;
use packages\port\adapter\services\cookie\CookieAuthTokenStore;

Route::get('/', function () {
    $store = new CookieAuthTokenStore();
    print_r($store->get());
});

Route::get('/login', [AuthenticationControllers::class, 'displayLoginPage']);
Route::get('/auth/callback', [AuthenticationControllers::class, 'login']);

Route::get('/profile/create', [CreateUserProfileController::class, 'displayForm']);
Route::post('/profile/create', [CreateUserProfileController::class, 'create']);

Route::get('/test', [TestController::class, 'index']);