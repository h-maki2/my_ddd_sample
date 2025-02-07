<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

class MessageProducer
{
    readonly RabbitMqQueue $queue;
    readonly Exchange $exchange;

    public function __construct(RabbitMqQueue $queue, Exchange $exchange)
    {
        $this->queue = $queue;
        $this->exchange = $exchange;
    }

    public function send(RabbitMqMessage $message): void
    {
        $this->queue->channel->basic_publish(
            $message->value,
            $this->exchange->exchangeName,
            $this->queue->routingKey
        );
    }

    public function close(): void
    {
        $this->exchange->close();
    }
}