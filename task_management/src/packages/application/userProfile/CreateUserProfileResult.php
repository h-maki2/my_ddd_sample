<?php

namespace packages\application\userProfile;

class CreateUserProfileResult
{
    readonly bool $isSuccess;
    readonly array $validationErrorMessageList;

    private function __construct(
        bool $isSuccess,
        array $validationErrorMessageList,
    ) {
        $this->isSuccess = $isSuccess;
        $this->validationErrorMessageList = $validationErrorMessageList;
    }

    public static function createWhenSuccess(): self
    {
        return new self(true, []);
    }

    public static function createWhenFailure(array $validationErrorMessageList): self
    {
        return new self(false, $validationErrorMessageList);
    }
}