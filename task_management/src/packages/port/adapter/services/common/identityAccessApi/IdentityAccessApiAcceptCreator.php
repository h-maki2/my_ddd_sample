<?php

namespace packages\port\adapter\services\common\identityAccessApi;

class IdentityAccessApiAcceptCreator
{
    public static function create(string $versionName): string
    {
        return 'application/vnd.identityaccess.' . $versionName . '+json';
    }
}