<?php

namespace packages\tests\helper\domain\model\project\projectMember;

use packages\domain\model\project\ProjectId;
use packages\domain\model\project\projectMember\ProjectMember;
use packages\domain\model\project\projectMember\ProjectParticipationStatus;
use packages\domain\model\project\projectMember\ProjectRole;
use packages\domain\model\userProfile\userAccount\UserId;
use packages\tests\helper\domain\model\userProfile\userAccount\TestUserIdFactory;

class TestProjectMemberFactory
{
    public static function create(
        ?UserId $userId = null,
        ?ProjectId $projectId = null,
        ?ProjectParticipationStatus $status = null,
        ?ProjectRole $role = null
    ): ProjectMember
    {
        return ProjectMember::reconstruct(
            $projectId ?? ProjectId::create('test-project-id'),
            $userId ?? TestUserIdFactory::create(),
            $status ?? ProjectParticipationStatus::Participated,
            $role ?? ProjectRole::Member
        );
    } 
}