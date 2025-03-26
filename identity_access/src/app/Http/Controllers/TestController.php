<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    public function test()
    {
        if (Auth::guard('web')->check()) {
            print 'aaaa';
        }

        print session()->get('user');
    }
}