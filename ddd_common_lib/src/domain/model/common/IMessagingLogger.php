<?php

namespace dddCommonLib\domain\model\common;

interface IMessagingLogger
{
    public function error(string $logMessage): void;
}