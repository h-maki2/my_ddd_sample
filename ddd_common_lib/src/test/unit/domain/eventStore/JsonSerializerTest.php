<?php

use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use PHPUnit\Framework\TestCase;

class JsonSerializerTest extends TestCase
{
    public function test_シリアライズしたオブジェクトをデシリアライズできる()
    {
        // given
        $event = new TestEvent();

        // when
        // イベントをシリアライズする
        $serializedEvent = JsonSerializer::serialize($event);

        // シリアライズしたイベントをデシリアライズする
        $deserializedEvent = JsonSerializer::deserialize($serializedEvent, TestEvent::class);

        // then
        $this->assertEquals($event->occurredOn(), $deserializedEvent->occurredOn());
        $this->assertEquals($event->eventVersion(), $deserializedEvent->eventVersion());
        $this->assertEquals($event->eventType(), $deserializedEvent->eventType());
    }
}