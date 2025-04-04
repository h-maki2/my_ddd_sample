<?php

namespace packages\port\adapter\persistence\eloquent;

use packages\domain\model\userProfile\IUserProfileRepository;
use packages\domain\model\userProfile\SelfIntroductionText;
use packages\domain\model\userProfile\UserName;
use packages\domain\model\userProfile\UserProfile;
use packages\domain\model\userProfile\UserProfileId;
use App\Models\UserProfile as EloquentUserProfile;
use packages\domain\model\userProfile\userAccount\UserEmail;
use packages\domain\model\userProfile\userAccount\UserId;
use RuntimeException;
use Ramsey\Uuid\Uuid;

class EloquentUserProfileRepository implements IUserProfileRepository
{
    public function findById(UserId $userId): ?UserProfile
    {
        $result = EloquentUserProfile::find($userId->value);

        if ($result === null) {
            return null;
        }

        return $this->toUserProfile($result);
    }

    public function save(UserProfile $userProfile): void
    {
        EloquentUserProfile::updateOrCreate(
            ['user_id' => $userProfile->userId()->value],
            [
                'name' => $userProfile->name()->value,
                'self_introduction_text' => $userProfile->selfIntroductionText()->value,
                'email' => $userProfile->userEmail()->value
            ]
        );
    }

    private function toUserProfile(object $record): UserProfile
    {
        return UserProfile::reconstruct(
            new UserId($record->user_id),
            new UserName($record->name),
            new SelfIntroductionText($record->self_introduction_text),
            new UserEmail($record->email),
        );
    }
}