<?php

namespace packages\adapter\presenter\userProfile\register\json;

use packages\adapter\presenter\common\json\HttpStatus;
use packages\adapter\presenter\common\json\JsonPresenter;
use packages\adapter\presenter\common\json\JsonResponseData;
use packages\application\userProfile\create\RegisterUserProfileOutputBoundary;
use packages\application\userProfile\create\CreateUserProfileResult;

class JsonRegisterUserProfilePresenter implements JsonPresenter
{
    private CreateUserProfileResult $CreateUserProfileResult;

    public function __construct(CreateUserProfileResult $CreateUserProfileResult)
    {
        $this->CreateUserProfileResult = $CreateUserProfileResult;
    }

    public function jsonResponseData(): JsonResponseData
    {
        return new JsonResponseData($this->responseData(), $this->httpStatus());
    }

    private function responseData(): array
    {
        if ($this->CreateUserProfileResult->isSuccess) {
            return [];
        }

        $responseData = [];
        foreach ($this->CreateUserProfileResult->validationErrorMessageList as $validationErrorMessage) {
            $responseData[$validationErrorMessage->fieldName] = $validationErrorMessage->errorMessageList;
        }
        return $responseData;
    }

    private function httpStatus(): HttpStatus
    {
        return $this->CreateUserProfileResult->isSuccess ? HttpStatus::Success : HttpStatus::BadRequest;
    }
}