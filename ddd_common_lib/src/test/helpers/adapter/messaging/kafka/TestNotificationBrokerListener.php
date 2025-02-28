<?php

namespace dddCommonLib\test\helpers\adapter\messaging\kafka;

use dddCommonLib\domain\model\notification\Notification;
use dddCommonLib\infrastructure\messaging\kafka\NotificationBrokerListener;
use dddCommonLib\test\helpers\domain\model\event\OtherTestEvent;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;

class TestNotificationBrokerListener extends NotificationBrokerListener
{
    private array $listenNotificationList = [];

    protected function filteredDispatch(Notification $notification): void
    {
        $this->listenNotificationList[] = $notification;

        $listenTestEvent = false;
        $listenOtherTestEvent = false;
        foreach ($this->listenNotificationList as $listenNotification) {
            if ($listenNotification->notificationType === TestEvent::class) {
                $listenTestEvent = true;
            }
            if ($listenNotification->notificationType === OtherTestEvent::class) {
                $listenOtherTestEvent = true;
            }
        }

        if ($listenTestEvent && $listenOtherTestEvent) {
            echo "全てのイベントがリッスンされました";
        }
    }

    protected function listensTo(): array
    {
        return [TestEvent::class, OtherTestEvent::class];
    }
}