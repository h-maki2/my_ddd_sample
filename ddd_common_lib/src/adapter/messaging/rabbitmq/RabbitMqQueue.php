<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

class RabbitMqQueue
{
    readonly AMQPChannel $channel;
    readonly string $queueName;
    readonly string $routingKey;
    private AMQPStreamConnection $connection;

    private const DLX_QUEUE_NAME = 'dlx_queue';
    private const DLX_ROUTING_KEY = 'dlx_routing_key';

    private function __construct(
        AMQPChannel $channel,
        string $queueName,
        string $routingKey,
        AMQPStreamConnection $connection
    )
    {
        $this->channel = $channel;
        $this->queueName = $queueName;
        $this->routingKey = $routingKey;
        $this->connection = $connection;
    }

    public static function fromInstanceWithBindExchange(
        Exchange $exchange,
        string $queueName,
        string $routingKey = ''
    ): self
    {
        if ($exchange->isTopic() && empty($routingKey)) {
            throw new InvalidArgumentException('トピックエクスチェンジの場合は、ルーティングキーを指定してください。');
        }
        $channel = $exchange->channel;
        $channel->queue_declare(
            $queueName, 
            false, 
            $exchange->isDurable, 
            false, 
            false,
            false,
            self::dlxSettingParams()
        );
        $channel->queue_bind($queueName, $exchange->exchangeName, $routingKey);
        return new self($channel, $queueName, $routingKey, $exchange->connection);
    }

    public static function declareDlxQueue(
        Exchange $exchange
    ): self
    {
        $channel = $exchange->channel;
        $channel->queue_declare(self::DLX_QUEUE_NAME, false, true, false, false);
        $channel->queue_bind(
            self::DLX_QUEUE_NAME, 
            $exchange->exchangeName, 
            self::DLX_ROUTING_KEY
        );
        return new self($channel, self::DLX_QUEUE_NAME, self::DLX_ROUTING_KEY, $exchange->connection);
    }

    private static function dlxSettingParams(): AMQPTable
    {
        return new AMQPTable([
            'x-dead-letter-exchange' => Exchange::dlxExchangeName(),
            'x-dead-letter-routing-key' => self::DLX_ROUTING_KEY
        ]);
    }

    public function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }
}