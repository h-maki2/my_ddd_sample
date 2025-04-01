<?php

namespace packages\adapter\presenter\authenticationAccount\fetch\json;

use packages\adapter\presenter\common\json\HttpStatus;
use packages\adapter\presenter\common\json\JsonResponseData;
use packages\application\authenticationAccount\fetch\FetchAuthenticationAccountResult;

class JsonFetchAuthenticationAccountPresenter
{
    private FetchAuthenticationAccountResult $result;

    public function __construct(FetchAuthenticationAccountResult $result)
    {
        $this->result = $result;
    }

    public function jsonResponseData(): JsonResponseData
    {
        return new JsonResponseData($this->responseData(), $this->httpStatusCode());
    }

    public function responseData(): array
    {
        if (!$this->result->accountExists) {
            return [];
        }

        return [
            'userId' => $this->result->userId,
            'email' => $this->result->userEmail,
        ];
    }

    private function httpStatusCode(): HttpStatus
    {
        return $this->result->accountExists ? HttpStatus::Success: HttpStatus::BadRequest;
    }
}