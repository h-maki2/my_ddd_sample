<?php

namespace packages\domain\model\authenticationAccount\password;

class Argon2HashPasswordManager implements IPasswordManager
{
    private const HASH_OPTIONS = [
        'memory_cost' => 65536,
        'time_cost' => 4, // 4回のハッシュ化
        'threads' => 1 // 1スレッド
    ];

    /**
     * Argon2アルゴリズムを用いてハッシュ化する
     */
    public function hash(string $value): string
    {
        return password_hash($value, PASSWORD_ARGON2ID, self::HASH_OPTIONS);
    }

    public function verify(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }
}