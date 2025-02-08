<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

interface IRabbitMqLogService
{
    public function log(RabbitMqMessage $message): void;
}