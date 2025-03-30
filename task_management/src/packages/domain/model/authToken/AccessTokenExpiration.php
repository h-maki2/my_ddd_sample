<?php

namespace packages\domain\model\authToken;

use DateTimeImmutable;
use InvalidArgumentException;

class AccessTokenExpiration
{
    readonly DateTimeImmutable $value;

    private function __construct(DateTimeImmutable $value) 
    {
        $this->value = $value;
    }

    public static function create(int $expirationTimeStamp): self
    {
        if ($expirationTimeStamp <= 0) {
            throw new InvalidArgumentException('アクセストークンの有効期限のタイムスタンプ値が不正です。');
        }

        $currentTimeStamp = (new DateTimeImmutable())->getTimestamp();
        $tokenExpiration = $currentTimeStamp + $expirationTimeStamp;
        return new self((new DateTimeImmutable())->setTimestamp($tokenExpiration));
    }

    public static function reconstruct(DateTimeImmutable $value): self
    {
        return new self($value);
    }

    public function stringValue(): string
    {
        return $this->value->format('Y-m-d H:i:s');
    }

    /**
     * アクセストークンの有効期限が切れているかどうかを判定
     */
    public function isExpired(DateTimeImmutable $currentDateTime): bool
    {
        return $this->value < $currentDateTime;
    }
}