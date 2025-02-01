<?php

namespace packages\domain\model\authenticationAccount\password;

use InvalidArgumentException;
use packages\domain\model\authenticationAccount\validation\UserPasswordValidation;

class UserPassword
{
    readonly string $hashedValue;

    private IPasswordManager $passwordManager;

    private function __construct(string $hashedValue, IPasswordManager $passwordManager)
    {
        $this->hashedValue = $hashedValue;
        $this->passwordManager = $passwordManager;
    }

    public static function create(string $value, IPasswordManager $passwordManager): self
    {
        $userPasswordValidation = new UserPasswordValidation($value);
        if (!$userPasswordValidation->validate()) {
            throw new InvalidArgumentException('無効なパスワードです。');
        }

        return new self($passwordManager->hash($value), $passwordManager);
    }

    public static function reconstruct($hashedValue, IPasswordManager $passwordManager): self
    {
        return new self($hashedValue, $passwordManager);
    }

    public function equals(string $inputedPassword): bool
    {
        return $this->passwordManager->verify($inputedPassword, $this->hashedValue);
    }
}