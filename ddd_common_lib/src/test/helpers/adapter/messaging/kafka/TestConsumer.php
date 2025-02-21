<?php

namespace dddCommonLib\test\helpers\adapter\messaging\kafka;

use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\infrastructure\messaging\kafka\KafkaConsumer;
use dddCommonLib\test\helpers\domain\model\event\OtherTestEvent;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use Exception;
use PHPUnit\Event\TestRunner\ExecutionAborted;

class TestConsumer extends KafkaConsumer
{
    public array $catchedMessageList = [];

    protected function filteredDispatch(Notification $notification): callable
    {
        return function ($notification) {
            $this->catchedMessageList[] = $notification;
            if ($this->canComplateDispatch()) {
                throw new Exception('処理を終了します。');
            }
        };
    }

    private function canComplateDispatch(): bool
    {
        $catchedTestEvent = false;
        $catchedOtherTestEvent = false;
        foreach ($this->catchedMessageList as $catchedMessage) {
            if ($catchedMessage->notificationType === TestEvent::class) {
                $catchedTestEvent = true;
            }

            if ($catchedMessage->notificationType === OtherTestEvent::class) {
                $catchedOtherTestEvent = true;
            }
        }

        return $catchedTestEvent && $catchedOtherTestEvent;
    }

    protected function listensTo(): array
    {
        return [TestEvent::class, OtherTestEvent::class];
    }
}