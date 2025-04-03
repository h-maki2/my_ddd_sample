<?php

namespace App\Http\Controllers\Api\v1\authentication;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use packages\adapter\presenter\common\json\HttpStatus;
use packages\adapter\presenter\common\json\JsonResponseData;
use packages\domain\service\authenticationAccount\AuthenticationService;

class AuthenticationController extends ApiController
{
    public function isLoggedIn(AuthenticationService $authService): JsonResponse
    {
        $isLoggedIn = $authService->loggedInUserId() === null ? false : true;

        $httpStatus = $isLoggedIn ? HttpStatus::Success : HttpStatus::Unauthorized;
        $jsonResponseData = new JsonResponseData(['isLoggedIn' => $isLoggedIn], $httpStatus);
        return response()->json($jsonResponseData->value, $jsonResponseData->httpStatusCode());
    }
}