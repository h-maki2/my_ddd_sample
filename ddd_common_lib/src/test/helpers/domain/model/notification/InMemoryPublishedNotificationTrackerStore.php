<?php

namespace dddCommonLib\test\helpers\domain\model\notification;

use dddCommonLib\domain\model\notification\IPublishedNotificationTrackerStore;
use dddCommonLib\domain\model\notification\PublishedNotificationTracker;

class InMemoryPublishedNotificationTrackerStore implements IPublishedNotificationTrackerStore
{
    private ?PublishedNotificationTracker $publishedNotificationTracker = null;

    public function publishedNotificationTracker(): ?PublishedNotificationTracker
    {
        return $this->publishedNotificationTracker ?? null;
    }

    public function save(PublishedNotificationTracker $publishedNotificationTracker): void
    {
        $this->publishedNotificationTracker = $publishedNotificationTracker;
    }
}