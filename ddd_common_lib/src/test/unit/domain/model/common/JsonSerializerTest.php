<?php

use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use PHPUnit\Framework\TestCase;

class JsonSerializerTest extends TestCase
{
    public function test_シリアライズしたイベントを配列の形式にデシリアライズできる()
    {
        // given
        $event = new TestEvent();
        $serializedEvent = JsonSerializer::serialize($event);

        // when
        $deserializedEventArray = JsonSerializer::deserializeToArray($serializedEvent);

        // then
        $this->assertEquals($event->occurredOn(), $deserializedEventArray['occurredOn']);
        $this->assertEquals($event->eventVersion(), $deserializedEventArray['eventVersion']);
    }
}