<?php

namespace App\Http\Controllers\userProfile;

use Illuminate\Http\Request;
use packages\application\userProfile\create\CreateUserProfileApplicationService;

class CreateUserProfileController
{
    public function displayForm()
    {
        return view('userProfile.createUserProfileForm');
    }

    public function create(Request $request, CreateUserProfileApplicationService $createUserProfileApplicationService)
    {
        $result = $createUserProfileApplicationService->create(
            $request->input('userName') ?? '',
            $request->input('selfIntroductionText') ?? ''
        );

        if ($result->isSuccess) {
            return redirect('/');
        }

        return redirect('/profile/create')
                ->withErrors($result->validationErrorMessageList)
                ->withInput();
    }
}