<?php

namespace App\Http\Controllers\login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\Request;
use packages\application\login\LoginApplicationService;
use packages\port\adapter\presenter\login\blade\BladeLoginPresenter;

class LoginControllers extends Controller
{
    public function displaLoginPage()
    {
        return view('login');
    }

    public function login(
        LoginApplicationService $appService,
        Request $request
    )
    {
        $result = $appService(
            $request->input('email') ?? '',
            $request->input('password') ?? ''
        );

        $presenter = new BladeLoginPresenter($result);

        if ($presenter->loginSuccess()) {
            return redirect($presenter->authenticationRequestUrl());
        }

        return redirect()
                ->back()
                ->withErrors($presenter->faildMessage())
                ->withInput();
    }
}