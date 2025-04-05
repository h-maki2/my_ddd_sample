<?php

namespace packages\domain\model\project\projectMember;

enum ProjectRole: string
{
    case Leader = '1';
    case Member = '2';

    public function stringValue(): string
    {
        return match ($this) {
            self::Leader => 'リーダー',
            self::Member => 'メンバー',
        };
    }

    public function isLeader(): bool
    {
        return $this === self::Leader;
    }
}