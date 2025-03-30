<?php

namespace packages\application\userProfile;

use packages\port\adapter\presenter\common\json\HttpStatus;

class CreateUserProfileResponseData
{
    readonly bool $isSuccess;
    readonly array $validationErrorMessageList;

    public function __construct(
        bool $isSuccess,
        array $validationErrorMessageList,
    ) {
        $this->isSuccess = $isSuccess;
        $this->validationErrorMessageList = $validationErrorMessageList;
    }
}