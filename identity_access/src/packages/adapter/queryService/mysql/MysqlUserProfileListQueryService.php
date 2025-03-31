<?php

namespace packages\adapter\queryService\mysql;

use Illuminate\Support\Facades\DB;
use packages\application\userProfile\fetchList\IUserProfileListQueryService;
use packages\application\userProfile\fetchList\UserProfileDto;
use packages\domain\model\authenticationAccount\UserId;

class MysqlUserProfileListQueryService implements IUserProfileListQueryService
{
    /**
     * @param UserId[] $userIds
     * @return UserProfileDto[]
     */
    public function fetchList(array $userIds): array
    {
        $results = DB::select("SELECT 
                        up.user_id,
                        up.name,
                        up.self_introduction_text,
                        ai.email
                    FROM 
                        user_profiles up
                    INNER JOIN
                        authentication_informations ai
                    ON up.user_id = ap.user_id
                    WHERE
                        up.user_id IN ?
                ",
                [$this->userIdStringList($userIds)]
            );
        
        return $this->userProfileDtoList($results);
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

    /**
     * @param object[] $results
     * @return UserProfileDto[]
     */
    private function userProfileDtoList(array $results): array
    {
        return array_map(function (object $result) {
            return new UserProfileDto(
                $result->user_id,
                $result->name,
                $result->self_introduction_text,
                $result->email
            );
        }, $results);
    }
}