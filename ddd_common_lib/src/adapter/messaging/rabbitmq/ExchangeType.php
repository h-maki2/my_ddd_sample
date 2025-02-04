<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

enum ExchangeType: string {
    case FANOUT = 'fanout';
    case DIRECT = 'direct';
    case TOPIC = 'topic';
    case HEADERS = 'headers';

    public function isFanout(): bool
    {
        return $this === self::FANOUT;
    }

    public function isDirect(): bool
    {
        return $this === self::DIRECT;
    }

    public function isTopic(): bool
    {
        return $this === self::TOPIC;
    }

    public function isHeaders(): bool
    {
        return $this === self::HEADERS;
    }
}