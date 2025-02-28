<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

abstract class AKafkaConsumer
{
    protected RdKafka\MessageListener $consumer;

    protected function __construct(RdKafka\MessageListener $consumer)
    {
        $this->consumer = $consumer;
    }

    abstract public function consume(): RdKafka\Message;

    abstract public function commit(RdKafka\Message $message): void;

    abstract protected function waitTimeMs(): int;
}