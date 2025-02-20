<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

enum KafkaEnableAuthCommit: string
{
    case Enable = 'true';
    case Disable = 'false';
}