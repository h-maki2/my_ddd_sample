<?php

use packages\domain\model\project\projectMember\ProjectRole;
use PHPUnit\Framework\TestCase;

class ProjectRoleTest extends TestCase
{
    public function test_役割がリーダーであるインスタンスを生成できる()
    {
        // when
        $projectRole = ProjectRole::Leader;

        // then
        $this->assertEquals('1', $projectRole->value);
        $this->assertEquals('リーダー', $projectRole->stringValue());
        $this->assertTrue($projectRole->isLeader());
    }

    public function test_役割がメンバーであるインスタンスを生成できる()
    {
        // when
        $projectRole = ProjectRole::Member;

        // then
        $this->assertEquals('2', $projectRole->value);
        $this->assertEquals('メンバー', $projectRole->stringValue());
        $this->assertFalse($projectRole->isLeader());
    }
}