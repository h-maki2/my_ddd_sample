<?php

namespace App\Http\Controllers;

class TestController extends Controller
{
    public function index()
    {
        print session()->get('user_id');
        print "\n";
        print session()->put('user_id', 'testID');
        print "\n";
    }
}