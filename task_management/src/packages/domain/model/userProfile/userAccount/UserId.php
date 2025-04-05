<?php

namespace packages\domain\model\userProfile\userAccount;

use InvalidArgumentException;

class UserId
{
    readonly string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('UserId cannot be empty');
        }

        $this->value = $value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }
}