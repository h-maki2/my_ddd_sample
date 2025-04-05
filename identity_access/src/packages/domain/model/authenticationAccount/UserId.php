<?php

namespace packages\domain\model\authenticationAccount;

use InvalidArgumentException;
use packages\domain\model\common\identifier\IdentifierFromUUIDver7;

class UserId
{
    readonly string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('ユーザーIDが空です。');
        }

        $this->value = $value;
    }

    public function equals(UserId $userId): bool
    {
        return $this->value === $userId->value;
    }
}