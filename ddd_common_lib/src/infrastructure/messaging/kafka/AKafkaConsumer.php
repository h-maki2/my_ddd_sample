<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

abstract class AKafkaConsumer
{
    protected RdKafka\BrokerListener $consumer;

    protected const WAIT_TIME_MS = 10000;

    protected function __construct(RdKafka\BrokerListener $consumer)
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