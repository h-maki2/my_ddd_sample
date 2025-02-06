<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use PhpAmqpLib\Message\AMQPMessage;
use RuntimeException;

class MessageProducer
{
    readonly Exchange $exchange;

    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;
    }

    public function send(string $message): void
    {
        if ($this->exchange->isFanout()) {
            $this->sendByFanout($message);
            return;
        }

        if ($this->exchange->isTopic()) {
            $this->sendByTopic($message);
            return;
        }
    }

    public function close(): void
    {
        $this->exchange->close();
    }

    private function deliveryMode(): int
    {
        return $this->exchange->isDurable ? 2 : 1;
    }

    private function sendByFanout(string $message): void
    {
        if (!$this->exchange->isFanout()) {
            throw new RuntimeException('エクスチェンジのタイプがファンアウトではありません。type: ' . $this->exchange->exchangeType->value);
        }

        $this->exchange->channel->basic_publish($this->sendingMessage($message), $this->exchange->exchangeName);
    }

    private function sendByTopic(string $message): void
    {
        if (!$this->exchange->isTopic()) {
            throw new RuntimeException('エクスチェンジのタイプがトピックではありません。type: ' . $this->exchange->exchangeType->value);
        }

        $this->exchange->channel->basic_publish(
            $this->sendingMessage($message), 
            $this->exchange->exchangeName, 
            $this->exchange->routingKey
        );
    }

    private function sendingMessage(string $message): AMQPMessage
    {
        return new AMQPMessage($message, [
            'delivery_mode' => $this->deliveryMode(),
            'application_headers' => RabbitMqRetryCount::initialize()->toAmqpTable()
        ]);
    }
}