<?php

namespace packages\test\helpers\domain\oauth\client;

use packages\domain\model\oauth\client\AClientData;

class ClientDataForTest extends AClientData
{
    protected function baseUrl(): string
    {
        return 'http://localhost:8080';
    }
}