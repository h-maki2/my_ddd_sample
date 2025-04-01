<?php

namespace App\Http\Controllers\Api\v1\authenticationAccount;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use packages\adapter\presenter\authenticationAccount\fetch\json\JsonFetchAuthenticationAccountPresenter;
use packages\application\authenticationAccount\fetch\FetchAuthenticationAccountApplicationService;

class AuthenticationAccountController extends ApiController
{
    public function fetchAuthAccount(Request $request, FetchAuthenticationAccountApplicationService $appService): JsonResponse
    {
        $result = $appService->handle($request->input('scope') ?? '');

        $presenter = new JsonFetchAuthenticationAccountPresenter($result);
        $jsonResponseData = $presenter->jsonResponseData();
        return response()->json($jsonResponseData->value, $jsonResponseData->httpStatusCode());
    }
}