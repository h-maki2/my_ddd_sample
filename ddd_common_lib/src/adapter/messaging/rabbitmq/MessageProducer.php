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

    public function send(string $sendMessage): void
    {
        if ($this->exchange->isFanout()) {
            $this->sendByFanout($sendMessage);
            return;
        }

        if ($this->exchange->isTopic()) {
            $this->sendByTopic($sendMessage);
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

    private function sendByFanout(string $sendMessage): void
    {
        if (!$this->exchange->isFanout()) {
            throw new RuntimeException('エクスチェンジのタイプがファンアウトではありません。type: ' . $this->exchange->exchangeType->value);
        }

        $message = new AMQPMessage($sendMessage, ['delivery_mode' => $this->deliveryMode()]);
        $this->exchange->channel->basic_publish($message, $this->exchange->exchangeName);
    }

    private function sendByTopic(string $sendMessage): void
    {
        if (!$this->exchange->isTopic()) {
            throw new RuntimeException('エクスチェンジのタイプがトピックではありません。type: ' . $this->exchange->exchangeType->value);
        }

        $message = new AMQPMessage($sendMessage, ['delivery_mode' => $this->deliveryMode()]);
        $this->exchange->channel->basic_publish($message, $this->exchange->exchangeName, $this->exchange->routingKey);
    }
}