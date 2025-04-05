<?php

namespace packages\domain\model\project\validation;

use packages\domain\model\common\validator\Validator;

class ProjectNameValidation extends Validator
{
    private const MAX_LENGTH = 100;

    private string $projectName;

    public function __construct(string $projectName)
    {
        $this->projectName = $projectName;   
    }

    public function validate(): bool
    {
        if ($this->projectName === '' || mb_strlen($this->projectName, 'UTF-8') > self::MAX_LENGTH) {
            $this->setErrorMessage('プロジェクト名は1文字以上100文字以内で入力してください。');
            return false;
        }

        return true;
    }

    public function fieldName(): string
    {
        return 'projectName';
    }
}