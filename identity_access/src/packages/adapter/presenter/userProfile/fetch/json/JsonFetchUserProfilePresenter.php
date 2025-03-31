<?php

namespace packages\adapter\presenter\userProfile\fetch\json;

use packages\adapter\presenter\common\json\HttpStatus;
use packages\adapter\presenter\common\json\JsonPresenter;
use packages\adapter\presenter\common\json\JsonResponseData;
use packages\application\userProfile\fetch\fetchLoggedInUserProfile\FetchLoggedInUserProfileResult;

class JsonFetchUserProfilePresenter implements JsonPresenter
{
    private FetchLoggedInUserProfileResult $result;

    public function __construct(FetchLoggedInUserProfileResult $result)
    {
        $this->result = $result;
    }

    public function jsonResponseData(): JsonResponseData
    {
        return new JsonResponseData($this->responseData(), $this->httpStatus());
    }

    public function responseData(): array
    {
        if (!$this->result->isExistsUserProfile) {
            return [];
        }

        return [
            'userId' => $this->result->userId,
            'userName' => $this->result->userName,
            'selfIntroductionText' => $this->result->selfIntroductionText,
            'email' => $this->result->email,
        ];
    }

    private function httpStatus(): HttpStatus
    {
        return $this->result->isExistsUserProfile ? HttpStatus::Success : HttpStatus::NoContent;
    }
}