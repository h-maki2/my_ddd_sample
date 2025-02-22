<?php

namespace dddCommonLib\domain\model\common;

interface IMessagingLogger
{
    public function log(string $logMessage): void;
}