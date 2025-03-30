<?php

use App\Http\Controllers\authentication\AuthenticationControllers;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    print('ログイン成功');
});

Route::get('/login', [AuthenticationControllers::class, 'displayLoginPage']);
Route::get('/auth/callback', [AuthenticationControllers::class, 'login']);

Route::get('/test', [TestController::class, 'index']);