<?php

namespace packages\tests\domain\model\authToken;

use InvalidArgumentException;

class RefreshToken
{
    readonly string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('リフレッシュトークン値が空です。');
        }
        $this->value = $value;
    }
}