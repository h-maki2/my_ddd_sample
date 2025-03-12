<?php

namespace packages\adapter\persistence\eloquent;

use dddCommonLib\domain\model\eventStore\StoredEvent;
use dddCommonLib\test\helpers\domain\model\event\OtherTestEvent;
use dddCommonLib\test\helpers\domain\model\event\TestEvent;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EloquentEventStoreTest extends TestCase
{
    use DatabaseTransactions;

    private EloquentEventStore $eloquentEventStore;

    public function setUp(): void
    {
        parent::setUp();
        $this->eloquentEventStore = new EloquentEventStore();
    }

    public function test_ドメインイベントを保存する()
    {
        // given
        $storedEvent1 = StoredEvent::fromDomainEvent(new TestEvent());
        $storedEvent2 = StoredEvent::fromDomainEvent(new TestEvent());
        $storedEvent3 = StoredEvent::fromDomainEvent(new OtherTestEvent());

        // when
        $this->eloquentEventStore->append($storedEvent1);
        $this->eloquentEventStore->append($storedEvent2);
        $this->eloquentEventStore->append($storedEvent3);

        // then
        $storedEventList = $this->eloquentEventStore->allStoredEventsSince(1);

        // 全てのイベントが保存されていることを確認する
        $this->assertCount(3, $storedEventList);

        // イベントの内容が保存されていることを確認する
        $this->assertEquals($storedEvent1->eventBody, $storedEventList[0]->eventBody);
        $this->assertEquals($storedEvent1->occurredOn, $storedEventList[0]->occurredOn);

        $this->assertEquals($storedEvent2->eventBody, $storedEventList[1]->eventBody);
        $this->assertEquals($storedEvent2->occurredOn, $storedEventList[1]->occurredOn);

        $this->assertEquals($storedEvent3->eventBody, $storedEventList[2]->eventBody);
        $this->assertEquals($storedEvent3->occurredOn, $storedEventList[2]->occurredOn);
    }
}