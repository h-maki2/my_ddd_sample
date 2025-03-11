<?php

use dddCommonLib\domain\model\notification\NotificationReader;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use dddCommonLib\test\helpers\domain\model\notification\TestNotificationFactory;
use PHPUnit\Framework\TestCase;

class NotificationReaderTest extends TestCase
{
    public function test_イベントのキー名を指定して値を取得できる()
    {
        // given
        $testDomainEvent = new TestEvent();
        $notification = TestNotificationFactory::createFromDomainEvent($testDomainEvent);
        $notificationReader = new NotificationReader($notification);

        // when
        $occuredOn = $notificationReader->eventStringValue('occurredOn');
        $eventVersion = $notificationReader->eventStringValue('eventVersion');

        // then
        $this->assertEquals($testDomainEvent->occurredOn(), $occuredOn);
        $this->assertEquals($testDomainEvent->eventVersion(), $eventVersion);
    }
}