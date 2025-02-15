<?php

namespace dddCommonLib\domain\model\notification;

class PublishedNotificationTracker
{
    private int $mostResentPublishedNotificationId;

    public function __construct(int $mostResentPublishedNotificationId)
    {
        $this->mostResentPublishedNotificationId = $mostResentPublishedNotificationId;
    }

    public static function initialize(): self
    {
        return new self(0);
    }

    public function mostResentPublishedNotificationId(): int
    {
        return $this->mostResentPublishedNotificationId;
    }

    public function updatePublishedNotificationId(int $mostResentPublishedNotificationId): void
    {
        $this->mostResentPublishedNotificationId = $mostResentPublishedNotificationId;
    }
}