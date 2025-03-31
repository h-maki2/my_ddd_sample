<?php

namespace App\Http\Controllers\Api\v1\userProfile;

use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use packages\adapter\presenter\userProfile\fetch\json\JsonFetchUserProfilePresenter;
use packages\adapter\presenter\userProfile\register\json\JsonRegisterUserProfilePresenter;
use packages\application\userProfile\create\CreateUserProfileApplicationService;
use packages\application\userProfile\fetch\FetchUserProfileApplicationService;

class UserProfileController
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function fetchLoggedInUserProfile(FetchUserProfileApplicationService $appService): JsonResponse
    {
        $result = $appService->fetchLoggedInUserProfile($this->request->query('scope') ?? '');

        $presenter = new JsonFetchUserProfilePresenter($result);
        $jsonResponseData = $presenter->jsonResponseData();
        return response()->json($jsonResponseData->value, $jsonResponseData->httpStatusCode());
    }


    public function create(CreateUserProfileApplicationService $appService): JsonResponse
    {
        $result = $appService->create(
            $this->request->input('name') ?? '',
            $this->request->input('selfIntroductionText') ?? '',
            $this->request->input('scope') ?? ''
        );

        $presenter = new JsonRegisterUserProfilePresenter($result);
        $jsonResponseData = $presenter->jsonResponseData();
        return response()->json($jsonResponseData->value, $jsonResponseData->httpStatusCode());
    }
}