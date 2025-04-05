<?php

use packages\domain\model\project\projectMember\ProjectParticipationStatus;
use PHPUnit\Framework\TestCase;

class ProjectParticipationStatusTest extends TestCase
{
    public function test_招待済みの状態でインスタンスを生成する()
    {
        // when
        $status = ProjectParticipationStatus::Invited;

        // then
        $this->assertEquals('1', $status->value);
        $this->assertEquals('招待済み', $status->stringValue());
        $this->assertTrue($status->isInvited());
        $this->assertFalse($status->isParticipated());
    }

    public function test_参加済みの状態でインスタンスを生成する()
    {
        // when
        $status = ProjectParticipationStatus::Participated;

        // then
        $this->assertEquals('2', $status->value);
        $this->assertEquals('参加済み', $status->stringValue());
        $this->assertFalse($status->isInvited());
        $this->assertTrue($status->isParticipated());
    }
}