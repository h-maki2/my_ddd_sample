<?php

namespace packages\domain\model\userProfile\validation;

use packages\domain\model\common\validator\Validator;
use packages\domain\model\userProfile\IUserProfileRepository;
use packages\domain\model\userProfile\UserName;
use packages\domain\model\userProfile\validation\UserNameFormatChecker;
use packages\domain\service\userProfile\UserProfileService;

class UserNameValidation extends Validator
{
    private string $userName;

    public function __construct(string $userName)
    {
        $this->userName = $userName;
    }

    public function validate(): bool
    {
        if (UserNameFormatChecker::invalidUserNameLength($this->userName)) {
            $this->setErrorMessage('ユーザー名は1文字以上50文字以内で入力してください。');
            return false;
        }
        
        if (UserNameFormatChecker::onlyWhiteSpace($this->userName)) {
            $this->setErrorMessage('ユーザー名に空白文字列のみは使用できません。');
            return false;
        }

        return true;
    }

    public function fieldName(): string
    {
        return 'userName';
    }
}