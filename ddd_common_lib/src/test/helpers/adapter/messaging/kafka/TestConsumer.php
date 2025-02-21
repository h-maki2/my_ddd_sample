<?php

namespace dddCommonLib\test\helpers\adapter\messaging\kafka;

use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\infrastructure\messaging\kafka\KafkaConsumer;
use dddCommonLib\test\helpers\domain\model\event\OtherTestEvent;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use Exception;

class TestConsumer extends KafkaConsumer
{
    private array $catchedMessageList = [];

    protected function filteredDispatch(Notification $notification): callable
    {
        return function ($notification) {
            $this->catchedMessageList[] = $notification;
            throw new Exception('メッセージを受信しました。');
        };
    }

    protected function listensTo(): array
    {
        return [TestEvent::class, OtherTestEvent::class];
    }
}