<?php

namespace packages\domain\model\authToken;

use DateTimeImmutable;
use InvalidArgumentException;

class AccessToken
{
    readonly string $value;
    private AccessTokenExpiration $expiration;

    public function __construct(
        string $value,
        AccessTokenExpiration $expiration
    )
    {
        if (empty($value)) {
            throw new InvalidArgumentException('アクセストークン値が空です。');
        }
        $this->value = $value;
        $this->expiration = $expiration;
    }

    public function expiration(): string
    {
        return $this->expiration->stringValue();
    }

    /**
     * アクセストークンの有効期限が切れているかどうかを判定
     */
    public function isExpired(): bool
    {
        return $this->expiration->isExpired(new DateTimeImmutable());
    }
}