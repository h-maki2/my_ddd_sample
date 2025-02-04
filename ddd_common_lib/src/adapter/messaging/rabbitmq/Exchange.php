<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

class Exchange
{
    readonly ConnectionSettings $connectionSettings;
    readonly string $exchangeName;
    readonly string $exchangeType;
    readonly bool $durable;

    private function __construct(
        ConnectionSettings $connectionSettings,
        string $exchangeName,
        string $exchangeType,
        bool $durable
    ) {
        $this->connectionSettings = $connectionSettings;
        $this->exchangeName = $exchangeName;
        $this->exchangeType = $exchangeType;
        $this->durable = $durable;
    }

    public static function fanOutInstance(
        ConnectionSettings $connectionSettings,
        string $exchangeName,
        bool $durable
    ): self
    {
        return new self($connectionSettings, $exchangeName, 'fanout', $durable);
    }
}