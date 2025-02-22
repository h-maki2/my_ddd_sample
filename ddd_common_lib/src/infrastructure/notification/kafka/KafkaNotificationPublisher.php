<?php

namespace dddCommonLib\infrastructure\notification\kafka;

use dddCommonLib\domain\model\eventStore\IEventStore;
use dddCommonLib\domain\model\notification\IPublishedNotificationTrackerStore;

class KafkaNotificationPublisher
{
    private IPublishedNotificationTrackerStore $publishedNotificationTrackerStore;
}