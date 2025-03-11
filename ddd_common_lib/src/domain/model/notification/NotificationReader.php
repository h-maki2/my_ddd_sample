<?php

namespace dddCommonLib\domain\model\notification;

use dddCommonLib\domain\model\common\JsonSerializer;
use InvalidArgumentException;

class NotificationReader
{
    private Notification $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function eventStringValue(string $keyName): string
    {
        $domainEventArray = JsonSerializer::deserializeToArray($this->notification->eventBody);

        if (!array_key_exists($keyName, $domainEventArray)) {
            throw new InvalidArgumentException('Key not found');
        }

        return $domainEventArray[$keyName];
    }
}