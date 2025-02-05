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
    readonly ?string $routingKey;

    private const DLX_EXCHANGE_NAME = 'dlx_exchange';
    private const DLX_QUEUE_NAME = 'dlx_queue';
    private const DLX_ROUTING_KEY = 'dlx_routing_key';

    private const HEADER_NAME = 'application_headers';
    private const RETRY_COUNT_KEY_NAME = 'retry_count';

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
        bool $isDurable,
        string $queueName
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
        $channel->queue_declare(
            $queueName, 
            false, 
            $isDurable, 
            false, 
            false,
            false,
            self::dlxSettingParams()
        );
        $channel->queue_bind($queueName, $exchangeName);
        return new self($exchangeName, ExchangeType::FANOUT, $isDurable, $channel, $connection);
    }

    public static function topicInstance(
        ConnectionSettings $connectionSettings,
        string $exchangeName,
        bool $isDurable,
        string $routingKey,
        string $queueName
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
        $channel->queue_declare(
            $queueName, 
            false, 
            $isDurable, 
            false, 
            false,
            false,
            self::dlxSettingParams()
        );
        $channel->queue_bind($queueName, $exchangeName, $routingKey);
        return new self($exchangeName, ExchangeType::TOPIC, $isDurable, $channel, $connection, $routingKey);
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
        $channel->queue_declare(self::DLX_QUEUE_NAME, false, true, false, false);
        $channel->queue_bind(
            self::DLX_QUEUE_NAME, 
            self::DLX_EXCHANGE_NAME, 
            self::DLX_ROUTING_KEY
        );
        return new self(self::DLX_EXCHANGE_NAME, ExchangeType::DIRECT, true, $channel, $connection, self::DLX_ROUTING_KEY);
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

    public function headerParams(): AMQPTable
    {
        return new AMQPTable([self::RETRY_COUNT_KEY_NAME => 0]);
    }

    public function headerName(): string
    {
        return self::HEADER_NAME;
    }

    private static function dlxSettingParams(): AMQPTable
    {
        return new AMQPTable([
            'x-dead-letter-exchange' => self::DLX_EXCHANGE_NAME,
            'x-dead-letter-routing-key' => self::DLX_ROUTING_KEY
        ]);
    }
}