<?php

namespace App\Http\Controllers\authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use packages\application\login\LoginApplicationService;
use packages\port\adapter\presenter\login\blade\BladeLoginPresenter;

class AuthenticationControllers extends Controller
{
    public function displayLoginPage()
    {
        return view('authentication.login');
    }

    public function login(
        LoginApplicationService $appService,
        Request $request
    )
    {
        $result = $appService->login(
            $request->input('email') ?? '',
            $request->input('password') ?? ''
        );

        $presenter = new BladeLoginPresenter($result);

        if ($presenter->loginSuccess()) {
            print_r($presenter->authenticationRequestUrl());
            //redirect($presenter->authenticationRequestUrl());
            return;
        }

        return redirect()
                ->back()
                ->withErrors($presenter->faildMessage())
                ->withInput();
    }
}