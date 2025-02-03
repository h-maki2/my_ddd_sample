<?php

use dddCommonLib\domain\model\DomainEvent;
use dddCommonLib\domain\model\DomainEventPublisher;
use dddCommonLib\domain\model\DomainEventSubscriber;
use dddCommonLib\test\helpers\event\TestEvent;
use dddCommonLib\test\helpers\event\TestEventSubscriber;
use PHPUnit\Framework\TestCase;

class DomainEventPublisherTest extends TestCase
{
    public function test_サブスクライブしたイベントをパブリッシュする()
    {
        // given
        // テスト用のイベントをサブスクライブに登録する
        $subscriber1 = new TestEventSubscriber();
        $subscriber2 = new TestEventSubscriber();
        DomainEventPublisher::instance()->subscribe($subscriber1);
        DomainEventPublisher::instance()->subscribe($subscriber2);

        // when
        // イベントをパブリッシュする
        DomainEventPublisher::instance()->publish(new TestEvent());

        // then
        // パブリッシュしたイベントがハンドリングされていることを確認する
        $this->assertTrue($subscriber1->handled);
        $this->assertTrue($subscriber2->handled);
    }

    public function test_パブリッシュしていないイベントはサブスクライブされない()
    {
        // given
        // テスト用のイベントをサブスクライブに登録する
        $対象のサブスクライブ = new TestEventSubscriber();
        $対象外のサブスクライブ = new NoTargetEventSubscriber();

        DomainEventPublisher::instance()->subscribe($対象のサブスクライブ);
        DomainEventPublisher::instance()->subscribe($対象外のサブスクライブ);

        // when
        // イベントをパブリッシュする
        DomainEventPublisher::instance()->publish(new TestEvent());

        // then
        // パブリッシュしたイベントがハンドリングされていることを確認する
        $this->assertTrue($対象のサブスクライブ->handled);

        // パブリッシュしていないイベントはハンドリングされていないことを確認する
        $this->assertFalse($対象外のサブスクライブ->handled);
    }

    public function test_サブスクライブしたイベントをリセットできる()
    {
        // given
        // テスト用のイベントをサブスクライブに登録する
        $subscriber1 = new TestEventSubscriber();
        DomainEventPublisher::instance()->subscribe($subscriber1);

        // when
        // サブスクライブしたイベントをリセットする
        DomainEventPublisher::instance()->reset();

        // イベントをパブリッシュする
        DomainEventPublisher::instance()->publish(new TestEvent());

        // then
        // パブリッシュしたイベントがハンドリングされていないことを確認する
        $this->assertFalse($subscriber1->handled);
    }
}

class NoTargetEventSubscriber implements DomainEventSubscriber
{
    public bool $handled = false;

    public function handleEvent(DomainEvent $aDomainEvent): void
    {
        $this->handled = true;
    }

    public function isSubscribedTo(DomainEvent $aDomainEvent): bool
    {
        return $this->subscribedToEventType() === $aDomainEvent->eventType();
    }

    public function subscribedToEventType(): string
    {
        return DomainEvent::class;
    }
}