<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    public function test()
    {
        print_r(session()->all());
        print Auth::id();
        if (Auth::check()) {
            print 'aaaaa';
        }
        return;
    }
}