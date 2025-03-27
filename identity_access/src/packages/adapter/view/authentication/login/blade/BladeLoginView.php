<?php

namespace packages\adapter\view\authentication\login\blade;

use packages\adapter\presenter\authentication\login\blade\BladeLoginPresenter;
use packages\application\authentication\login\LoginResult;

class BladeLoginView
{
    private LoginResult $result;

    public function __construct(LoginResult $result)
    {
        $this->result = $result;
    }

    public function response()
    {
        if ($this->result->loginSucceeded) {
            return $this->successResponse();
        }

        return $this->faildResponse();
    }

    private function successResponse()
    {
        return redirect($this->result->authorizationUrl);
    }

    private function faildResponse()
    {
        return redirect()
                ->back()
                ->withErrors($this->faildResponseData())
                ->withInput();
    }

    private function faildResponseData(): array
    {
        return [
            'loginFaild' => true,
            'accountLocked' => $this->result->accountLocked
        ];
    }
}