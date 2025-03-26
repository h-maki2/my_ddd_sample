<?php

use App\Http\Controllers\LoginController as ControllersLoginController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Web\authentication\login\LoginController;
use App\Http\Controllers\Web\registration\DefinitiveRegistrationController;
use App\Http\Controllers\Web\registration\ProvisionalRegistrationController;
use App\Http\Controllers\Web\registration\ResendDefinitiveRegistrationConfirmation;
use App\Models\AuthenticationInformation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;

Route::get('/provisional_register', [ProvisionalRegistrationController::class, 'userRegisterForm']);
Route::post('/provisional_register', [ProvisionalRegistrationController::class, 'userRegister']);

Route::get('/definitive_register', [DefinitiveRegistrationController::class, 'definitiveRegistrationCompletedForm']);
Route::post('/definitive_register', [DefinitiveRegistrationController::class, 'definitiveRegistrationCompleted']);

Route::get('/resend', [ResendDefinitiveRegistrationConfirmation::class, 'resendDefinitiveRegistrationConfirmationForm']);
Route::post('/resend', [ResendDefinitiveRegistrationConfirmation::class, 'resendDefinitiveRegistrationConfirmation']);

Route::get('login', function () {
    $user = AuthenticationInformation::find('0195be8e-bd52-72ea-bcc5-44caa24a7f94');
    Auth::loginUsingId('0195be8e-bd52-72ea-bcc5-44caa24a7f94', true);
});

Route::middleware(['auth:web'])->group(function () {
    Route::get('/test', function () {
        return 'aaaa';
    });
});