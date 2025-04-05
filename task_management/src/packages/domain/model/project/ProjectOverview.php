<?php

namespace packages\domain\model\project;

use InvalidArgumentException;
use packages\domain\model\project\validation\ProjectOverviewValidation;

class ProjectOverview
{
    readonly string $value;

    public function __construct(string $value)
    {
        $validation = new ProjectOverviewValidation($value);
        if (!$validation->validate()) {
            throw new InvalidArgumentException('プロジェクト概要が不正です。value: ' . $value);
        }
        $this->value = $value;
    }
}