<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

class Exchange
{
    readonly string $exchangeName;
    readonly ExchangeType $exchangeType;
    readonly bool $isDurable;
    readonly AMQPChannel $channel;
    private AMQPStreamConnection $connection;

    private const DLX_EXCHANGE_NAME = 'dlx_exchange';

    private function __construct(
        string $exchangeName,
        ExchangeType $exchangeType,
        bool $isDurable,
        AMQPChannel $channel,
        AMQPStreamConnection $connection
    ) {
        $this->exchangeName = $exchangeName;
        $this->exchangeType = $exchangeType;
        $this->isDurable = $isDurable;
        $this->channel = $channel;
        $this->connection = $connection;
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
        $channel->exchange_declare($exchangeName, ExchangeType::TOPIC, false, $isDurable, false);
        return new self($exchangeName, ExchangeType::TOPIC, $isDurable, $channel, $connection);
    }

    public static function dlxInstance(
        ConnectionSettings $connectionSettings
    ): self
    {
        $connection = new AMQPStreamConnection(
            $connectionSettings->hostName, 
            $connectionSettings->port, 
            $connectionSettings->userName, 
            $connectionSettings->password
        );
        $channel = $connection->channel();
        $channel->exchange_declare(
            self::DLX_EXCHANGE_NAME, 
            ExchangeType::DIRECT, 
            false, 
            true, 
            false
        );
        return new self(self::DLX_EXCHANGE_NAME, ExchangeType::DIRECT, true, $channel, $connection);
    }

    public function queueInstance(
        string $queueName,
        string $routingKey = ''
    ): RabbitMqQueue
    {
        return RabbitMqQueue::declareQueue($this, $queueName, $routingKey);
    }

    public function dlxQueueInstance(): RabbitMqQueue
    {
        return RabbitMqQueue::declareDlxQueue($this);
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