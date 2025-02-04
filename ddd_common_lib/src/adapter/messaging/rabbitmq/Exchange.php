<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Exchange
{
    readonly string $exchangeName;
    readonly ExchangeType $exchangeType;
    readonly bool $isDurable;
    readonly AMQPChannel $channel;

    private function __construct(
        string $exchangeName,
        ExchangeType $exchangeType,
        bool $isDurable,
        AMQPChannel $channel
    ) {
        $this->exchangeName = $exchangeName;
        $this->exchangeType = $exchangeType;
        $this->isDurable = $isDurable;
        $this->channel = $channel;
    }

    public static function fanOutInstance(
        ConnectionSettings $connectionSettings,
        string $exchangeName,
        bool $isDurable
    ): self
    {
        $channel = self::exchangeDeclare($connectionSettings, $exchangeName, $isDurable, ExchangeType::FANOUT);
        return new self($exchangeName, ExchangeType::FANOUT, $isDurable, $channel);
    }

    private static function exchangeDeclare(
        ConnectionSettings $connectionSettings,
        string $exchangeName,
        bool $isDurable,
        ExchangeType $exchangeType
    ): AMQPChannel
    {
        $connection = new AMQPStreamConnection(
            $connectionSettings->hostName, 
            $connectionSettings->port, 
            $connectionSettings->userName, 
            $connectionSettings->password
        );
        $channel = $connection->channel();
        $channel->exchange_declare($exchangeName, $exchangeType->value, false, $isDurable, false);
        return $channel;
    }
}