<?php

namespace dddCommonLib\domain\model\eventStore;

interface IEventStore
{
    /**
     * @return StoredEvent[]
     */
    public function allStoredEventsBetween(string $aLowStoredEventId, string $aHighStoredEventId): array;

    /**
     * @return StoredEvent[]
     */
    public function allStoredEventsSince(string $aStoredEventId): array;

    public function append(StoredEvent $aStoredEvent): void;

    public function countStoredEvents(): int;
}