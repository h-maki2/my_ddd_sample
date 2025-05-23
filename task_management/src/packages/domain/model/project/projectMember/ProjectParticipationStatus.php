<?php

namespace packages\domain\model\project\projectMember;

enum ProjectParticipationStatus: string
{
    case Invited = '1';
    case Participated = '2';

    public function stringValue(): string
    {
        return match ($this) {
            self::Invited => '招待済み',
            self::Participated => '参加済み',
        };
    }

    public function isParticipated(): bool
    {
        return $this === self::Participated;
    }

    public function isInvited(): bool
    {
        return $this === self::Invited;
    }
}