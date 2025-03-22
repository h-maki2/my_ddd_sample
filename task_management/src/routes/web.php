<?php

use App\Http\Controllers\authentication\AuthenticationControllers;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthenticationControllers::class, 'displaLoginPage']);
Route::post('/login', [AuthenticationControllers::class, 'login']);
