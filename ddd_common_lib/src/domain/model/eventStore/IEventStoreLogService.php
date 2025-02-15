<?php

namespace dddCommonLib\domain\model\eventStore;

interface IEventStoreLogService
{
    public function log(StoredEvent $storedEvent, string $errorMessage): void;
}