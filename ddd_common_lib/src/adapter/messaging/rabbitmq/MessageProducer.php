<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

class MessageProducer
{
    readonly Exchange $exchange;

    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;
    }

    public function send(
        RabbitMqMessage $message,
        string $routingKey = ''
    ): void
    {
        $this->exchange->channel->basic_publish(
            $message->value,
            $this->exchange->exchangeName,
            $routingKey
        );
    }

    public function close(): void
    {
        $this->exchange->close();
    }
}