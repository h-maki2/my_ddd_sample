<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

abstract class ExchangeListener
{
    protected RabbitMqQueue $queue;
    protected MessageConsumer $consumer;

    public function __construct()
    {
        $this->attachToQueue();
        $this->registerConsumer();
    }

    public function handle(): void
    {
        $this->consumer->listen();
    }

    abstract protected function exchangeName(): string;

    abstract protected function queueName(): string;

    abstract protected function connectionSettings(): ConnectionSettings;

    abstract protected function  listensTo(): array;

    abstract protected function filteredDispatch(): callable;

    protected function queue(): RabbitMqQueue
    {
        return $this->queue;
    }

    protected function attachToQueue(): void
    {
        $this->queue = RabbitMqQueue::fromInstance(
            $this->connectionSettings(),
            $this->queueName(),
            true
        );
    }

    protected function registerConsumer(): void
    {
        $this->consumer = new MessageConsumer(
            $this->queue,
            $this->exchangeName(),
            $this->listensTo(),
            $this->filteredDispatch()
        );
    }
}