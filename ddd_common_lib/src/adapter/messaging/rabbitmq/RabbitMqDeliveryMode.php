<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

enum RabbitMqDeliveryMode: int
{
    case PERSISTENT = 2;
    case NON_PERSISTENT = 1;
}