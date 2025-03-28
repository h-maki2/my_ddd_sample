<?php

namespace packages\domain\model\authToken;

use DateTimeImmutable;
use InvalidArgumentException;

class AccessToken
{
    readonly string $value;
    private DateTimeImmutable $expiresIn;

    public function __construct(
        string $value,
        int $expiresTimeStamp
    )
    {
        if (empty($value)) {
            throw new InvalidArgumentException('アクセストークン値が空です。');
        }
        $this->value = $value;

        $expiresDateTime = (new DateTimeImmutable())->setTimestamp($expiresTimeStamp);
        if ($expiresDateTime === false) {
            throw new InvalidArgumentException('アクセストークンの有効期限が不正です。');
        }
        $this->expiresIn = $expiresDateTime;
        if ($this->isExpired()) {
            throw new InvalidArgumentException('アクセストークンの有効期限が切れています。');
        }
    }

    public function expiresIn(): int
    {
        return $this->expiresIn->getTimestamp();
    }

    /**
     * アクセストークンの有効期限が切れているかどうかを判定
     */
    public function isExpired(): bool
    {
        return $this->expiresIn < new DateTimeImmutable();
    }
}