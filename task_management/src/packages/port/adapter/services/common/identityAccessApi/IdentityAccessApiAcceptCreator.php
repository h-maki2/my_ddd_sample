<?php

namespace packages\port\adapter\services\common\identityAccessApi;

class IdentityAccessApiAcceptCreator
{
    public static function create(IdentityAccessApiVersion $version): string
    {
        return 'application/vnd.identityaccess.' . $version->value . '+json';
    }
}