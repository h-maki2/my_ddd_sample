<?php

namespace packages\domain\model\auth;

use InvalidArgumentException;

class OneTimeToken
{
    readonly string $value;

    private function __construct(string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('value is empty');
        }
        $this->value = $value;
    }

    public static function create(): self
    {
        return new self(bin2hex(random_bytes(32)));
    }

    public static function reconstruct(string $value): self
    {
        return new self($value);
    }

    public function equals(OneTimeToken $other): bool
    {
        return $this->value === $other->value;
    }
}