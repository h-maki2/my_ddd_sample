<?php

namespace packages\adapter\persistence\eloquent;

use dddCommonLib\domain\model\eventStore\IEventStore;
use dddCommonLib\domain\model\eventStore\StoredEvent;
use App\Models\StoredEvent as StoredEventModel;
use Illuminate\Database\Eloquent\Collection;

class EloquentEventStore implements IEventStore
{
    public function storedEventFromId(int $aStoredEventId): ?StoredEvent
    {
        $result = StoredEventModel::find($aStoredEventId);
        if ($result === null) {
            return null;
        }

        return $this->toStoredEvent($result);
    }

    /**
     * @return StoredEvent[]
     */
    public function allStoredEventsSince(int $aStoredEventId): array
    {
        $results = StoredEventModel::where('event_id', '>', $aStoredEventId)->get();
        return $this->toStoredEventList($results);
    }

    public function append(StoredEvent $event): void
    {
        StoredEventModel::create([
            'event_body' => $event->eventBody,
            'occurred_on' => $event->occurredOn,
            'type_name' => $event->eventType,
        ]);
    }

    /**
     * @return StoredEvent[]
     */
    private function toStoredEventList(Collection $results): array
    {
        $storedEvents = [];
        foreach ($results as $result) {
            $storedEvents[] = $this->toStoredEvent($result);
        }

        return $storedEvents;
    }

    private function toStoredEvent(StoredEventModel $result): StoredEvent
    {
        return StoredEvent::reconstruct(
            $result->type_name,
            $result->occurred_on,
            $result->event_body,
            $result->event_id
        );
    }
}