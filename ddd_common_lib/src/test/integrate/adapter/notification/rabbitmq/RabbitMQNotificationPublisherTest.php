<?php

use dddCommonLib\adapter\notification\rabbitmq\RabbitMQNotificationPublisher;
use dddCommonLib\test\helpers\domain\model\eventStore\InMemoryEventStore;

class RabbitMQNotificationPublisherTest
{
    private InMemoryEventStore $eventStore;
    private RabbitMQNotificationPublisher $publisher;
}