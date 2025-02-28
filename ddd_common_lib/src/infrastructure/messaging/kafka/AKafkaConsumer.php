<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

abstract class AKafkaConsumer
{
    protected RdKafka\BrokerListener $consumer;

    protected function __construct(RdKafka\BrokerListener $consumer)
    {
        $this->consumer = $consumer;
    }

    abstract public function consume(): RdKafka\Message;

    abstract public function commit(RdKafka\Message $message): void;

    abstract protected function waitTimeMs(): int;
}