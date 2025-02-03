<?php

use dddCommonLib\domain\model\common\JsonSerializer;
use dddCommonLib\domain\model\eventStore\StoredEvent;
use dddCommonLib\test\helpers\event\TestEvent;
use PHPUnit\Framework\TestCase;

class StoredEventTest extends TestCase
{
    public function test_ドメインイベントからインスタンスを生成後に、ドメインイベントを復元できる()
    {
        // given
        $testEvent = new TestEvent();

        // when
        // ドメインイベントからインスタンスを生成
        $storedEvent = StoredEvent::fromDomainEvent($testEvent);
        
        // storedEventインスタンスからドメインイベントを復元
        $reconstructedTestEvent = $storedEvent->toDomainEvent();

        // then
        // ドメインイベントを正しく復元出来ていることを確認する
        $this->assertEquals($testEvent->occurredOn()->format('Y-m-d'), $reconstructedTestEvent->occurredOn()->format('Y-m-d'));
        $this->assertEquals($testEvent->eventVersion(), $reconstructedTestEvent->eventVersion());
        $this->assertEquals($testEvent->eventType(), $reconstructedTestEvent->eventType());
    }

    public function test_reconstructメソッドでインスタンスを生成後に、ドメインイベントを復元できる()
    {
        // given
        // テスト用のイベントを生成する
        $testEvent = new TestEvent();
        $eventBody = JsonSerializer::serialize($testEvent);
        $eventType = TestEvent::class;
        $occurredOn = $testEvent->occurredOn();

        // when
        // StoredEventのインスタンス生成
        $storedEvent = StoredEvent::reconstruct($eventType, $occurredOn, $eventBody, '1');

        // ドメインイベントを復元する
        $reconstructedTestEvent = $storedEvent->toDomainEvent();

        // then
        // ドメインイベントを正しく復元出来ていることを確認する
        $this->assertEquals($testEvent->occurredOn()->format('Y-m-d'), $reconstructedTestEvent->occurredOn()->format('Y-m-d'));
        $this->assertEquals($testEvent->eventVersion(), $reconstructedTestEvent->eventVersion());
        $this->assertEquals($testEvent->eventType(), $reconstructedTestEvent->eventType());
    }
}