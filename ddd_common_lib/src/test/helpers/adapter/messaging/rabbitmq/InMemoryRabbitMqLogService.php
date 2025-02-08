<?php

namespace dddCommonLib\test\helpers\adapter\messaging\rabbitmq;

use dddCommonLib\adapter\messaging\rabbitmq\IRabbitMqLogService;
use dddCommonLib\adapter\messaging\rabbitmq\RabbitMqMessage;

class InMemoryRabbitMqLogService implements IRabbitMqLogService
{
    private array $logList = [];

    public function log(RabbitMqMessage $message): void
    {
        $this->logList[$message->deliveryTag()] = $message;
    }

    public function findAll(): array
    {
        return $this->logList;
    }
}