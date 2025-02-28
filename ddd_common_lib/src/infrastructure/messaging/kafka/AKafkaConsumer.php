<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

use RdKafka;

abstract class AKafkaConsumer
{
    protected RdKafka\KafkaConsumer $consumer;

    protected const WAIT_TIME_MS = 10000;

    protected function __construct(RdKafka\KafkaConsumer $consumer)
    {
        $this->consumer = $consumer;
    }

    abstract public function consume(): RdKafka\Message;

    abstract public function commit(RdKafka\Message $message): void;

    protected function waitTimeMs(): int
    {
        return self::WAIT_TIME_MS;
    }
}