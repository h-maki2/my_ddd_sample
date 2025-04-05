<?php

namespace packages\domain\model\project;

enum ProjectStatus: string
{
    case NotStarted = '1';
    case OnGoing = '2';
    case Completed = '3';

    public function stringValue(): string
    {
        return match ($this) {
            self::NotStarted => '未着手',
            self::OnGoing => '進行中',
            self::Completed => '完了',
        };
    }
}