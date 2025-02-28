<?php

namespace packages\test\helpers\domain\authenticationAccount\password;

use packages\domain\model\authenticationAccount\password\IPasswordManager;

class Md5PasswordManager implements IPasswordManager
{
    public function hash(string $password): string
    {
        return md5($password);
    }

    public function verify(string $password, string $hashedPassword): bool
    {
        return md5($password) === $hashedPassword;
    }   
}