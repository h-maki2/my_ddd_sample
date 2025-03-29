<?php

namespace App\Http\Controllers\Api\v1\userProfile;

use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use packages\adapter\presenter\userProfile\register\json\JsonRegisterUserProfilePresenter;
use packages\application\userProfile\register\RegisterUserProfileApplicationService;

class UserProfileController
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function register(RegisterUserProfileApplicationService $appService): JsonResponse
    {
        $result = $appService->register(
            $this->request->input('name') ?? '',
            $this->request->input('selfIntroductionText') ?? '',
            $this->request->input('scope') ?? ''
        );

        $presenter = new JsonRegisterUserProfilePresenter($result);
        $jsonResponseData = $presenter->jsonResponseData();
        return response()->json($jsonResponseData->value, $jsonResponseData->httpStatusCode());
    }
}