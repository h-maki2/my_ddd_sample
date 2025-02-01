<?php

namespace packages\domain\model\authenticationAccount\password;

interface IPasswordManager
{
    public function hash(string $password): string;
    public function verify(string $password, string $hashedPassword): bool;
}