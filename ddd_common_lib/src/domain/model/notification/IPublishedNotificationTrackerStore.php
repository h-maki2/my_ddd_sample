<?php

namespace dddCommonLib\domain\model\notification;

interface IPublishedNotificationTrackerStore
{
    public function publishedNotificationTracker(): ?PublishedNotificationTracker;
    public function save(PublishedNotificationTracker $publishedNotificationTracker): void;
}