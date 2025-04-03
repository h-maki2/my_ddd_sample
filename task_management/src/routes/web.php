<?php

use App\Http\Controllers\authentication\AuthenticationController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\userProfile\CreateUserProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    print_r('Hello World');
});

Route::get('/login', [AuthenticationController::class, 'displayLoginPage']);
Route::get('/auth/callback', [AuthenticationController::class, 'login']);

Route::get('/profile/create', [CreateUserProfileController::class, 'displayForm']);
Route::post('/profile/create', [CreateUserProfileController::class, 'create']);

Route::get('/test', [TestController::class, 'index']);