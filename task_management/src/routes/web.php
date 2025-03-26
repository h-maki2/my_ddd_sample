<?php

use App\Http\Controllers\authentication\AuthenticationControllers;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthenticationControllers::class, 'displayLoginPage']);
Route::post('/login', [AuthenticationControllers::class, 'login']);

Route::get('/test', [TestController::class, 'index']);