<?php

namespace packages\domain\model\project;

use packages\domain\model\project\validation\ProjectNameValidation;

class ProjectName
{
    readonly string $value;

    public function __construct(string $value)
    {
        $validation = new ProjectNameValidation($value);
        if (!$validation->validate()) {
            throw new \InvalidArgumentException('プロジェクト名が不正です。 value: ' . $value);
        }
        $this->value = $value;
    }
}