<?php

namespace packages\adapter\presenter\authentication\login\json;

use packages\adapter\presenter\common\json\HttpStatus;
use packages\adapter\presenter\common\json\JsonResponseData;
use packages\application\authentication\login\LoginResult;

class JsonLoginPresenter
{
    private LoginResult $result;

    public function __construct(LoginResult $result)
    {
        $this->result = $result;
    }

    public function jsonResponseData(): JsonResponseData
    {
        return new JsonResponseData($this->responseData(), $this->httpStatusCode());
    }

    private function responseData(): array
    {
        if ($this->result->loginSucceeded) {
            return [
                'authorizationUrl' => $this->result->authorizationUrl
            ];
        }

        return [
            'accountLocked' => $this->result->accountLocked
        ];
    }

    private function httpStatusCode(): HttpStatus
    {
        return $this->result->loginSucceeded ? HttpStatus::Success : HttpStatus::Unauthorized;
    }
}