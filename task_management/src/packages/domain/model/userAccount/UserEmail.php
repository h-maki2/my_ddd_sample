<?php

namespace packages\domain\model\userAccount;

use InvalidArgumentException;

class UserEmail
{
    readonly string $value;

    public function __construct(string $value)
    {
        if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $value)) {
            throw new InvalidArgumentException('メールアドレスの形式が不正です。');
        }

        $this->value = $value;
    }
}