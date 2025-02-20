<?php

namespace dddCommonLib\infrastructure\messaging\kafka;

enum KafkaAcks: string
{
    case NONE = '0';
    case LEADER = '1';
    case ALL = 'all';
}