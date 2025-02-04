<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Exchange
{
    readonly string $exchangeName;
    readonly ExchangeType $exchangeType;
    readonly bool $isDurable;
    readonly AMQPChannel $channel;
    private AMQPStreamConnection $connection;
    readonly ?string $routingKey;

    private function __construct(
        string $exchangeName,
        ExchangeType $exchangeType,
        bool $isDurable,
        AMQPChannel $channel,
        AMQPStreamConnection $connection,
        ?string $routingKey = null
    ) {
        $this->exchangeName = $exchangeName;
        $this->exchangeType = $exchangeType;
        $this->isDurable = $isDurable;
        $this->channel = $channel;
        $this->connection = $connection;
        $this->routingKey = $routingKey;
    }

    public static function fanOutInstance(
        ConnectionSettings $connectionSettings,
        string $exchangeName,
        bool $isDurable
    ): self
    {
        $connection = new AMQPStreamConnection(
            $connectionSettings->hostName, 
            $connectionSettings->port, 
            $connectionSettings->userName, 
            $connectionSettings->password
        );
        $channel = $connection->channel();
        $channel->exchange_declare($exchangeName, ExchangeType::FANOUT, false, $isDurable, false);
        return new self($exchangeName, ExchangeType::FANOUT, $isDurable, $channel, $connection);
    }

    public static function topicInstance(
        ConnectionSettings $connectionSettings,
        string $exchangeName,
        bool $isDurable,
        string $routingKey
    ): self
    {
        $connection = new AMQPStreamConnection(
            $connectionSettings->hostName, 
            $connectionSettings->port, 
            $connectionSettings->userName, 
            $connectionSettings->password
        );
        $channel = $connection->channel();
        $channel->exchange_declare($exchangeName, ExchangeType::TOPIC, false, $isDurable, false);
        return new self($exchangeName, ExchangeType::TOPIC, $isDurable, $channel, $connection, $routingKey);
    }

    public function setQueue(string $queueName): void
    {
        if ($queueName === '') {
            throw new InvalidArgumentException('キュー名が空です。');
        }

        $this->channel->queue_declare($queueName, false, $this->isDurable, false, false);
        $this->channel->queue_bind($queueName, $this->exchangeName);
    }

    public function isFanout(): bool
    {
        return $this->exchangeType->isFanout();
    }

    public function isTopic(): bool
    {
        return $this->exchangeType->isTopic();
    }

    public function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }
}