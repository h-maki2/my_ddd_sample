<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

use RuntimeException;

class MessageProducer
{
    readonly Exchange $exchange;

    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;
    }

    public function send(RabbitMqMessage $message): void
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

    public function deliveryMode(): RabbitMqDeliveryMode
    {
        return $this->exchange->isDurable ? RabbitMqDeliveryMode::PERSISTENT : RabbitMqDeliveryMode::NON_PERSISTENT;
    }

    private function sendByFanout(RabbitMqMessage $message): void
    {
        if (!$this->exchange->isFanout()) {
            throw new RuntimeException('エクスチェンジのタイプがファンアウトではありません。type: ' . $this->exchange->exchangeType->value);
        }

        if (!$this->deliveryMode()->equals($message->deliveryMode())) {
            throw new RuntimeException('delivery_modeが一致しません。メッセージのdelivery_mode: ' . $message->deliveryMode()->value . 'エクスチェンジのdelivery_mode' . $this->deliveryMode()->value);
        }

        $this->exchange->channel->basic_publish($message->value, $this->exchange->exchangeName);
    }

    private function sendByTopic(RabbitMqMessage $message): void
    {
        if (!$this->exchange->isTopic()) {
            throw new RuntimeException('エクスチェンジのタイプがトピックではありません。type: ' . $this->exchange->exchangeType->value);
        }

        if (!$this->deliveryMode()->equals($message->deliveryMode())) {
            throw new RuntimeException('delivery_modeが一致しません。メッセージのdelivery_mode: ' . $message->deliveryMode()->value . 'エクスチェンジのdelivery_mode' . $this->deliveryMode()->value);
        }

        $this->exchange->channel->basic_publish(
            $message->value, 
            $this->exchange->exchangeName, 
            $this->exchange->routingKey
        );
    }
}