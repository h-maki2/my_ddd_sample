<?php

namespace packages\port\adapter\services\common\identityAccessApi;

class IdentityAccessApiAcceptCreator
{
    public static function create(IdentityAccessApiVersion $versionName): string
    {
        return 'application/vnd.identityaccess.' . $versionName . '+json';
    }
}