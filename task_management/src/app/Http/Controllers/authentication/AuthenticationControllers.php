<?php

namespace App\Http\Controllers\authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use packages\application\login\LoginApplicationService;
use packages\port\adapter\presenter\login\blade\BladeLoginPresenter;

class AuthenticationControllers extends Controller
{
    private LoginApplicationService $loginApplicationService;

    public function __construct(LoginApplicationService $loginApplicationService)
    {
        $this->loginApplicationService = $loginApplicationService;
    }

    public function displayLoginPage()
    {
        $loginUrl = $this->loginApplicationService->createLoginUrl();
        return view('authentication.login', ['loginUrl' => $loginUrl]);
    }
}