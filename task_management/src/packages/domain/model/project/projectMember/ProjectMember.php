<?php

namespace packages\domain\model\project\projectMember;

use InvalidArgumentException;
use packages\domain\model\project\ProjectId;
use packages\domain\model\userProfile\userAccount\UserId;
use packages\domain\service\userProfile\UserProfileService;

class ProjectMember
{
    readonly ProjectId $projectId;
    readonly UserId $userId;
    readonly ProjectParticipationStatus $status;
    readonly ProjectRole $role;

    private function __construct(
        ProjectId $projectId,
        UserId $userId,
        ProjectParticipationStatus $status,
        ProjectRole $role
    ) {
        $this->projectId = $projectId;
        $this->userId = $userId;
        $this->status = $status;
        $this->role = $role;
    }

    /**
     * プロジェクトメンバーをリーダーとして作成
     */
    public static function createWithLeaderRole(
        ProjectId $projectId,
        UserId $userId,
        UserProfileService $userProfileService
    ): self 
    {
        if (!$userProfileService->isExists($userId)) {
            throw new InvalidArgumentException('登録されていないユーザーIDが指定されました。userId: ' . $userId->value);
        }

        return new self(
            $projectId,
            $userId,
            ProjectParticipationStatus::Participated,
            ProjectRole::Leader
        );
    }

    /**
     * プロジェクトメンバーをメンバーとして作成
     */
    public static function createWithMemberRole(
        ProjectId $projectId,
        UserId $userId,
        UserProfileService $userProfileService
    ): self 
    {
        if (!$userProfileService->isExists($userId)) {
            throw new InvalidArgumentException('登録されていないユーザーIDが指定されました。userId: ' . $userId->value);
        }

        return new self(
            $projectId,
            $userId,
            ProjectParticipationStatus::Invited,
            ProjectRole::Member
        );
    }

    public static function reconstruct(
        ProjectId $projectId,
        UserId $userId,
        ProjectParticipationStatus $status,
        ProjectRole $role
    ): self 
    {
        return new self(
            $projectId,
            $userId,
            $status,
            $role
        );
    }

    /**
     * プロジェクトメンバーの参加ステータスを参加済みに変更
     */
    public function updateToParticipated(): void
    {
        $this->status = ProjectParticipationStatus::Participated;
    }

    /**
     * プロジェクトメンバーの役割をリーダーに変更
     */
    public function changeRoleToLeader(): void
    {
        if (!$this->isParticipated()) {
            throw new InvalidArgumentException('参加していないユーザーの役割を変更することはできません。');
        }

        $this->role = ProjectRole::Leader;
    }

    public function equals(ProjectId $projectId, UserId $userId): bool
    {
        return $this->projectId->equals($projectId) && $this->userId->equals($userId);
    }

    /**
     * プロジェクトメンバーが参加済みかどうかを判定
     */
    private function isParticipated(): bool
    {
        return $this->status->isParticipated();
    }
}