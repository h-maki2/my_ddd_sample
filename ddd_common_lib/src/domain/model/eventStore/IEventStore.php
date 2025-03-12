<?php

namespace dddCommonLib\domain\model\eventStore;

interface IEventStore
{
    public function storedEventFromId(string $aStoredEventId): ?StoredEvent;

    /**
     * @return StoredEvent[]
     */
    public function allStoredEventsSince(string $aStoredEventId): array;

    public function append(StoredEvent $aStoredEvent): void;
}