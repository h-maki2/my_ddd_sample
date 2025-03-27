<?php

namespace App\Http\Controllers\Web\authentication\login;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use packages\adapter\presenter\authentication\login\blade\BladeLoginPresenter;
use packages\adapter\presenter\authentication\login\json\JsonLoginPresenter;
use packages\adapter\view\authentication\login\blade\BladeLoginView;
use packages\application\authentication\displayLoginForm\DisplayLoginFormApplicationService;
use packages\application\authentication\login\LoginApplicationService;
use packages\application\authentication\login\LoginInputBoundary;

class LoginController extends Controller
{
    public function displayLoginForm()
    {

    }

    public function login(Request $request, LoginApplicationService $appService)
    {
        $result = $appService->login(
            $request->input('email') ?? '',
            $request->input('password') ?? '',
            $request->input('client_id') ?? '',
            $request->input('redirect_url') ?? '',
            $request->input('response_type') ?? '',
            $request->input('state') ?? '',
            $request->input('scope') ?? ''
        );

        $view = new BladeLoginView($result);
        return $view->response();
    }
}