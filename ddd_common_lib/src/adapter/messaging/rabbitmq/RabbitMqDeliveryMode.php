<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

enum RabbitMqDeliveryMode: int
{
    case PERSISTENT = 2;
    case NON_PERSISTENT = 1;

    public function equals(RabbitMqDeliveryMode $otherDeliveryMode): bool
    {
        return $this->value === $otherDeliveryMode->value;
    }
}