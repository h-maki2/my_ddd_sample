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
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $this->request;
    }

    public function displayLoginForm()
    {
        return view('authentication.login', [
            'clientId' => $this->request->query('client_id', ''),
            'redirectUrl' => $this->request->query('redirect_url', ''),
            'responseType' => $this->request->query('response_type', ''),
            'state' => $this->request->query('state', ''),
            'scope' => $this->request->query('scope', '')
        ]);
    }

    public function login(LoginApplicationService $appService)
    {
        $result = $appService->login(
            $this->request->input('email') ?? '',
            $this->request->input('password') ?? '',
            $this->request->input('client_id') ?? '',
            $this->request->input('redirect_url') ?? '',
            $this->request->input('response_type') ?? '',
            $this->request->input('state') ?? '',
            $this->request->input('scope') ?? ''
        );

        $view = new BladeLoginView($result);
        return $view->response();
    }
}