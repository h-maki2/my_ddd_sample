<?php

use packages\domain\model\project\ProjectId;
use packages\domain\model\project\projectMember\ProjectMember;
use packages\domain\model\project\projectMember\ProjectRole;
use packages\domain\service\userProfile\UserProfileService;
use packages\port\adapter\persistence\inMemory\InMemoryUserProfileRepository;
use packages\tests\helper\domain\model\userProfile\UserProfileTestDataCreator;
use PHPUnit\Framework\TestCase;

class ProjectMemberTest extends TestCase
{
    private UserProfileTestDataCreator $userProfileTestDataCreator;
    private UserProfileService $userProfileService;

    protected function setUp(): void
    {
        $userProfileRepository = new InMemoryUserProfileRepository();
        $this->userProfileTestDataCreator = new UserProfileTestDataCreator(
            $userProfileRepository
        );
        $this->userProfileService = new UserProfileService(
            $userProfileRepository
        );
    }

    public function test_役割がリーダーであるインスタンスを生成する()
    {
        // given
        // ユーザープロフィールを作成する
        $userProfile = $this->userProfileTestDataCreator->create();
        $projectId = ProjectId::create('test-project-id');

        // when
        $projectMember = ProjectMember::createWithLeaderRole(
            $projectId,
            $userProfile->userId(),
            $this->userProfileService
        );

        // then
        $this->assertEquals($projectId, $projectMember->projectId);
        $this->assertEquals($userProfile->userId(), $projectMember->userId);

        // リーダーの役割であることを確認
        $this->assertTrue($projectMember->isLeader());

        // 参加済みの状態であることを確認
        $this->assertTrue($projectMember->status->isParticipated());
    }

    public function test_役割がメンバーであるインスタンスを生成する()
    {
        // given
        // ユーザープロフィールを作成する
        $userProfile = $this->userProfileTestDataCreator->create();
        $projectId = ProjectId::create('test-project-id');

        // when
        $projectMember = ProjectMember::createWithMemberRole(
            $projectId,
            $userProfile->userId(),
            $this->userProfileService
        );

        // then
        $this->assertEquals($projectId, $projectMember->projectId);
        $this->assertEquals($userProfile->userId(), $projectMember->userId);

        // メンバーの役割であることを確認
        $this->assertEquals(ProjectRole::Member, $projectMember->role);

        // 招待済みのステータスであることを確認
        $this->assertTrue($projectMember->status->isInvited());
    }

    public function test_ステータスを招待済みから参加済みに更新する()
    {
        // given
        // ユーザープロフィールを作成する
        $userProfile = $this->userProfileTestDataCreator->create();
        $projectId = ProjectId::create('test-project-id');

        // 招待済みのステータスのプロジェクトメンバー作成する
        
    }
}