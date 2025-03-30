<?php

namespace App\Http\Controllers\userProfile;

use Illuminate\Support\Facades\Request;
use packages\application\userProfile\CreateUserProfileApplicationService;

class CreateUserProfileController
{
    public function create(Request $request, CreateUserProfileApplicationService $createUserProfileApplicationService): void
    {
        $result = $createUserProfileApplicationService->create(
            $request->input('name') ?? '',
            $request->input('selfIntroductionText') ?? ''
        );
    }

}