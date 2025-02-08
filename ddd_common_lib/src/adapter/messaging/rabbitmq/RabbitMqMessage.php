<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use PhpAmqpLib\Message\AMQPMessage;

class RabbitMqMessage
{
    readonly AMQPMessage $value;

    private const HEADER_KEY = 'application_headers';
    private const DELIVERY_MODE_KEY = 'delivery_mode';

    private function __construct(AMQPMessage $value)
    {
        $this->value = $value;
    }

    public static function fromInstance(
        string $message,
        RabbitMqDeliveryMode $deliveryMode
    ): self
    {
        $amqpMessage = new AMQPMessage($message, [
            self::DELIVERY_MODE_KEY => $deliveryMode->value,
            self::HEADER_KEY => RabbitMqRetryCount::initialize()->toAmqpTable()
        ]);

        return new self($amqpMessage);
    }

    public function retrieve(): self
    {
        $amqpMessage = new AMQPMessage($this->value->body, [
            self::DELIVERY_MODE_KEY => $this->value->get(self::DELIVERY_MODE_KEY),
            self::HEADER_KEY => $this->retryCount()->increment()->toAmqpTable()
        ]);

        return new self($amqpMessage);
    }

    public static function reconstruct(AMQPMessage $value): self
    {
        return new self($value);
    }
    
    public function deliveryMode(): RabbitMqDeliveryMode
    {
        return RabbitMqDeliveryMode::from($this->value->get(self::DELIVERY_MODE_KEY));
    }

    public function hasReachedMaxRetryCount(): bool
    {
        return $this->retryCount()->hasReachedMaxRetryCount();
    }

    public function deliveryTag(): int
    {
        return $this->value->get('delivery_tag');
    }

    public function messageBody(): string
    {
        return $this->value->body;
    }

    private function retryCount(): RabbitMqRetryCount
    {
        $headers = $this->value->get(self::HEADER_KEY);
        $retryCount = $headers->getNativeData()[RabbitMqRetryCount::key()];
        return RabbitMqRetryCount::reconstruct($retryCount);
    }
}