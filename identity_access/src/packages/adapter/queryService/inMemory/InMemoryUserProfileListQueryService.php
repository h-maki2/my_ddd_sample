<?php

namespace packages\adapter\queryService\inMemory;

use packages\application\userProfile\fetchList\IUserProfileListQueryService;
use packages\application\userProfile\fetchList\UserProfileDto;
use packages\domain\model\authenticationAccount\UserId;

class InMemoryUserProfileListQueryService implements IUserProfileListQueryService
{
    private array $userProfileDtoList = [];

    /**
     * @param UserId[] $userIds
     * @return UserProfileDto[]
     */
    public function fetchList(array $userIds): array
    {
        $userIdStringList = $this->userIdStringList($userIds);

        foreach ($this->userProfileDtoList as $userProfileDto) {
            if (in_array($userProfileDto->userId, $userIdStringList)) {
                $results[] = $userProfileDto;
            }
        }

        return $results;
    }

    public function setTestData(UserProfileDto $userProfileDto): void
    {
        $this->userProfileDtoList[] = $userProfileDto;
    }

    /**
     * @param UserId[] $userIds
     */
    private function userIdStringList(array $userIds): array
    {
        return array_map(function (UserId $userId) {
            return $userId->value;
        }, $userIds);
    }
}