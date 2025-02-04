<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

class MessageConsumer
{
    private Exchange $exchange;

    public function __construct(
        Exchange $exchange,
        string $queueName,
    )
    {
        $this->exchange = $exchange->setQueue($queueName);
    }
}