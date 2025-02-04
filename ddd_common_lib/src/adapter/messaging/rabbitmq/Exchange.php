<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Exchange
{
    readonly string $exchangeName;
    readonly string $exchangeType;
    readonly bool $isDurable;
    readonly AMQPChannel $channel;

    private function __construct(
        string $exchangeName,
        string $exchangeType,
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
        $exchangeType = 'fanout';
        $channel = self::exchangeDeclare($connectionSettings, $exchangeName, $isDurable, $exchangeType);
        return new self($exchangeName, $exchangeType, $isDurable, $channel);
    }

    private static function exchangeDeclare(
        ConnectionSettings $connectionSettings,
        string $exchangeName,
        bool $isDurable,
        string $exchangeType
    ): AMQPChannel
    {
        $connection = new AMQPStreamConnection(
            $connectionSettings->hostName, 
            $connectionSettings->port, 
            $connectionSettings->userName, 
            $connectionSettings->password
        );
        $channel = $connection->channel();
        $channel->exchange_declare($exchangeName, $exchangeType, false, $isDurable, false);
        return $channel;
    }
}