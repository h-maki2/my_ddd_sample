<?php

namespace dddCommonLib\test\helpers\adapter\messaging\kafka;

use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\infrastructure\messaging\kafka\KafkaMessageConsumer;
use dddCommonLib\test\helpers\domain\model\event\OtherTestEvent;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use Exception;
use PHPUnit\Event\TestRunner\ExecutionAborted;

class TestConsumer extends KafkaMessageConsumer
{
    public array $catchedMessageList = [];

    protected function filteredDispatch(Notification $notification): void
    {
        $this->catchedMessageList[] = $notification;
        if ($this->canComplateDispatch()) {
            throw new Exception('処理を終了します。');
        }
    }

    public function listenEvent(string $eventType): bool
    {
        foreach ($this->catchedMessageList as $catchedMessage) {
            if ($catchedMessage->notificationType === $eventType) {
                return true;
            }
        }

        return false;
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