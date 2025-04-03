<?php

namespace App\Http\Controllers\userProfile;

use Illuminate\Http\Request;
use packages\application\userProfile\update\UpdateUserProfileApplicationService;

class UpdateUserProfileController
{
    private UpdateUserProfileApplicationService $appService;

    public function __construct(UpdateUserProfileApplicationService $appService)
    {
        $this->appService = $appService;
    }

    public function displayForm()
    {
        $result = $this->appService->displayUpdateUserProfileForm();

        return view('userProfile.updateUserProfileForm', [
            'userName' => $result->userName,
            'selfIntroductionText' => $result->selfIntroductionText,
        ]);
    }
}