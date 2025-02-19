<?php

namespace dddCommonLib\infrastructure\messaging\rabbitmq;

use PhpAmqpLib\Wire\AMQPTable;

class RabbitMqRetryCount
{
    private int $retryCount;

    private const TABLE_KEY = 'retry_count';

    private const MAX_RETRY_COUNT = 3;

    private function __construct($retryCount)
    {
        $this->retryCount = $retryCount;
    }

    public static function initialize(): self
    {
        return new self(0);
    }

    public static function reconstruct(int $retryCount): self
    {
        return new self($retryCount);
    }

    public static function key(): string
    {
        return self::TABLE_KEY;
    }

    public function increment(): self
    {
        return new self($this->retryCount + 1);
    }

    public function hasReachedMaxRetryCount(): bool
    {
        return $this->retryCount >= self::MAX_RETRY_COUNT;
    }

    public function toAmqpTable(): AMQPTable
    {
        return new AMQPTable([self::TABLE_KEY => $this->retryCount]);
    }
}