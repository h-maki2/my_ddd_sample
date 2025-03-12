<?php

namespace dddCommonLib\domain\model\eventStore;

interface IEventStore
{
    public function storedEventFromId(int $aStoredEventId): ?StoredEvent;

    /**
     * @return StoredEvent[]
     */
    public function allStoredEventsSince(int $aStoredEventId): array;

    public function append(StoredEvent $aStoredEvent): void;
}