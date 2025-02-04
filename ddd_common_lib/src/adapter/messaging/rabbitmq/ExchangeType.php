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
}