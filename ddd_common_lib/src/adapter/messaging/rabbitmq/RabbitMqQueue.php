<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Wire\AMQPTable;

class RabbitMqQueue
{
    private AMQPChannel $channel;
    private string $queueName;
    private AMQPStreamConnection $connection;

    private const DLX_QUEUE_NAME = 'dlx_queue';
    private const DLX_ROUTING_KEY = 'dlx_routing_key';

    private function __construct(
        AMQPChannel $channel,
        string $queueName,
        AMQPStreamConnection $connection
    )
    {
        $this->channel = $channel;
        $this->queueName = $queueName;
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
        return new self($channel, $queueName, $exchange->connection);
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
        return new self($channel, self::DLX_QUEUE_NAME, $exchange->connection);
    }

    private static function dlxSettingParams(): AMQPTable
    {
        return new AMQPTable([
            'x-dead-letter-exchange' => Exchange::dlxExchangeName(),
            'x-dead-letter-routing-key' => self::DLX_ROUTING_KEY
        ]);
    }

    public function consume(
        callable $filteredDispatch
    ): void
    {
        $this->channel->basic_consume(
            $this->queueName, 
            '', 
            false, 
            false, 
            false, 
            false, 
            $filteredDispatch
        );
    }

    public function run(
        int $waitSeconds,
        int $sleepSeconds
    )
    {
        while ($this->channel->is_consuming()) {
            try {
                $this->channel->wait(null, false, $waitSeconds); // 5秒間メッセージを待つ
            } catch (AMQPTimeoutException $e) {
                sleep($sleepSeconds);
            }
        }
    }

    public function ack(int $deliveryTag): void
    {
        $this->channel->basic_ack($deliveryTag);
    }

    public function nack(int $deliveryTag): void
    {
        $this->channel->basic_nack($deliveryTag, false, false);
    }

    public function requeueMessage(RabbitMqMessage $message): void
    {
        $this->channel->basic_publish($message->value, '', $this->queueName);
    }

    public function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }
}