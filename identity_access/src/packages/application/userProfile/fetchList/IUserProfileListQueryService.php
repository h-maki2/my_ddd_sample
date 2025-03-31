<?php

namespace packages\application\userProfile\fetchList;

interface IUserProfileListQueryService
{
    /**
     * @param UserId[] $userIds
     * @return UserProfileDto[]
     */
    public function fetchList(array $userIds): array;
}