<?php

namespace dddCommonLib\test\unit\domain\eventStore;

use dddCommonLib\domain\eventStore\EventSerializer;
use dddCommonLib\test\helpers\event\TestEvent;
use PHPUnit\Framework\TestCase;

class EventSerializerTest extends TestCase
{
    public function test_シリアライズしたドメインイベントをデシリアライズできる()
    {
        // given
        // シリアライズするドメインイベントを用意する
        $testEvent = new TestEvent();

        // when
        $serializedEvent = EventSerializer::serialize($testEvent);
        print_r($serializedEvent);

        // then
        // シリアライズしたドメインイベントをデシリアライズできることを確認する
        $this->assertEquals($testEvent, EventSerializer::deserialize($serializedEvent));
    }
}