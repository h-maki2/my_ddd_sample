<?php

namespace packages\domain\model\project\validation;

use packages\domain\model\common\validator\Validator;

class ProjectOverviewValidation extends Validator
{
    private const MAX_LENGTH = 500;

    private string $projectOverview;

    public function __construct(string $projectOverview)
    {
        $this->projectOverview = $projectOverview;
    }

    public function validate(): bool
    {
        if (mb_strlen($this->projectOverview) > self::MAX_LENGTH) {
            $this->setErrorMessage('プロジェクト概要は500文字以内で入力してください。');
            return false;
        }

        return true;
    }

    public function fieldName(): string
    {
        return 'projectOverview';
    }
}