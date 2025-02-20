<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

enum KafkaAutoOffsetReset: string
{
    case EARLIEST = 'earliest';
    case LATEST = 'latest';
    case NONE = 'none';
}