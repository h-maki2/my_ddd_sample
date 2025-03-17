<?php

namespace packages\adapter\messaging\kafka;

use dddCommonLib\domain\model\common\IMessagingLogger;
use Illuminate\Support\Facades\Log;

class LaravelMessagingLogger implements IMessagingLogger
{
    public function error(string $logMessage): void
    {
        Log::error($logMessage);
    }
}