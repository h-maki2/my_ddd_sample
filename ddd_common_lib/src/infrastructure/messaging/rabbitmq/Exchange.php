<?php

namespace dddCommonLib\infrastructure\messaging\rabbitmq;

use dddCommonLib\infrastructure\messaging\rabbitmq\exceptions\NotExistsQueueException;
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
    readonly AMQPStreamConnection $connection;

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
        $this->channel->confirm_select();
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
        $exchangeType = ExchangeType::FANOUT;
        $channel->exchange_declare($exchangeName, $exchangeType->value, false, $isDurable, false);
        return new self($exchangeName, $exchangeType, $isDurable, $channel, $connection);
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
        $exchangeType = ExchangeType::TOPIC;
        $channel->exchange_declare($exchangeName, $exchangeType->value, false, $isDurable, false);
        return new self($exchangeName, $exchangeType, $isDurable, $channel, $connection);
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
        $exchangeType = ExchangeType::DIRECT;
        $channel->exchange_declare(
            self::DLX_EXCHANGE_NAME, 
            $exchangeType->value, 
            false, 
            true, 
            false
        );
        return new self(self::DLX_EXCHANGE_NAME, $exchangeType, true, $channel, $connection);
    }

    public function publish(
        RabbitMqMessage $message,
        string $routingKey = ''
    )
    {
        $this->channel->basic_publish(
            $message->value,
            $this->exchangeName,
            $routingKey,
            true
        );
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

    public static function dlxExchangeName(): string
    {
        return self::DLX_EXCHANGE_NAME;
    }
}