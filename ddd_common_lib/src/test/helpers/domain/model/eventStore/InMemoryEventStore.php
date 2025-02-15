<?php

namespace dddCommonLib\test\helpers\domain\model\eventStore;

use dddCommonLib\domain\model\eventStore\IEventStore;
use dddCommonLib\domain\model\eventStore\StoredEvent;

class InMemoryEventStore implements IEventStore
{
    private array $storedEventList = [];

    public function allStoredEventsSince(string $aStoredEventId): array
    {
        $result = [];
        foreach ($this->storedEventList as $storedEventId => $storedEvent) {
            if ($storedEventId <= $aStoredEventId) {
                continue;
            }

            $result[] = StoredEvent::reconstruct(
                $storedEvent->eventType,
                $storedEvent->occurredOn,
                $storedEvent->eventBody,
                $storedEventId
            );
        }

        return $result;
    }

    public function append(StoredEvent $storedEvent): void
    {
        $this->storedEventList[$this->nextStoredEventId()] = $storedEvent;
    }

    private function nextStoredEventId(): int
    {
        return count($this->storedEventList) + 1;
    }
}