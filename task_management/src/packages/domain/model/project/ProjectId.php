<?php

namespace packages\domain\model\project;

use InvalidArgumentException;

class ProjectId
{
    readonly string $value;

    private const PREFIX = 'TA-P-';

    private function __construct(string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('プロジェクトIDが空です。');
        }

        if (!str_starts_with($value, self::PREFIX)) {
            throw new InvalidArgumentException('プロジェクトIDが不正です。value: ' . $value);
        }

        if ($value === self::PREFIX) {
            throw new InvalidArgumentException('プロジェクトIDが不正です。value: ' . $value);
        }

        $this->value = $value;
    }

    public static function create(string $identifier): self
    {
        return new self(self::PREFIX . $identifier);
    }

    public static function reconstruct(string $value): self
    {
        return new self($value);
    }

    public function equals(ProjectId $other): bool
    {
        return $this->value === $other->value;
    }
}