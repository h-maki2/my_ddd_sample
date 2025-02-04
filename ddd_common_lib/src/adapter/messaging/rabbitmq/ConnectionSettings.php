<?php

namespace dddCommonLib\adapter\messaging\rabbitmq;

class ConnectionSettings
{
    readonly string $hostName;
    readonly string $userName;
    readonly string $password;
    readonly string $port;

    public function __construct(
        string $hostName,
        string $userName,
        string $password,
        string $port
    ) {
        $this->hostName = $hostName;
        $this->userName = $userName;
        $this->password = $password;
        $this->port = $port;
    }
}