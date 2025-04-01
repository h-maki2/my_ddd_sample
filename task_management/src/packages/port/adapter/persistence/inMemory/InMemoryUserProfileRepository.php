<?php

namespace packages\adapter\persistence\inMemory;

use packages\domain\model\authenticationAccount\UserId;
use packages\domain\model\userProfile\IUserProfileRepository;
use packages\domain\model\userProfile\SelfIntroductionText;
use packages\domain\model\userProfile\UserName;
use packages\domain\model\userProfile\UserProfile;
use packages\domain\model\userProfile\UserProfileId;
use RuntimeException;
use Ramsey\Uuid\Uuid;

class InMemoryUserProfileRepository implements IUserProfileRepository
{
    private array $userProfiles = [];

    public function findById(UserId $userId): ?UserProfile
    {
        foreach ($this->userProfiles as $userProfile) {
            if ($userProfile->user_id === $userId->value) {
                return $this->toUserProfile($userProfile);
            }
        }

        return null;
    }

    public function save(UserProfile $userProfile): void
    {
        $this->userProfiles[$userProfile->userId()->value] = (object) [
            'user_id' => $userProfile->userId()->value,
            'name' => $userProfile->name()->value,
            'self_introduction_text' => $userProfile->selfIntroductionText()->value,
        ];
    }

    private function toUserProfile(object $record): UserProfile
    {
        return UserProfile::reconstruct(
            new UserId($record->user_id),
            new UserName($record->name),
            new SelfIntroductionText($record->self_introduction_text)
        );
    }
}