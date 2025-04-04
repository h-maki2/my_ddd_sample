<?php

namespace App\Http\Controllers\userProfile;

use Illuminate\Http\Request;
use packages\application\userProfile\create\CreateUserProfileApplicationService;

class CreateUserProfileController
{
    private CreateUserProfileApplicationService $createUserProfileApplicationService;

    public function __construct(CreateUserProfileApplicationService $createUserProfileApplicationService)
    {
        $this->createUserProfileApplicationService = $createUserProfileApplicationService;
    }

    public function displayForm()
    {
        $this->createUserProfileApplicationService->displayCreateUserProfileForm();
        return view('userProfile.createUserProfileForm');
    }

    public function create(Request $request)
    {
        $result = $this->createUserProfileApplicationService->create(
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