<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

class MessageConsumer
{
    private Exchange $exchange;

    public function __construct(
        Exchange $exchange,
    )
    {
        $this->exchange = $exchange;
    }
}