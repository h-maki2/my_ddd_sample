<?php

namespace App\Http\Controllers;

use Laravel\Passport\Client;
use App\Http\Controllers\Controller;
use App\Models\authenticationAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        // 認証が成功した場合、セッションにユーザーを保存
        // print_r($b->user_id);
        // print_r(session()->all());
        // print "\n";
        print_r(session()->all());
        print Auth::id();
        print "\n";
        Auth::guard('web')->loginUsingId('0195be8e-bd52-72ea-bcc5-44caa24a7f94');
        session()->put('test_session', 'test');
        print Auth::id();
        print "\n";
        print_r(session()->all());
        print "\n";
        if (Auth::check()) {
            print 'aaaaa';
        }

        $client = Client::where('id', '1')->first();
        // return response()->json([
        //     'authorization_url' => url('/oauth/authorize?response_type=code&client_id='.$client->id.'&redirect_uri='.$request->redirect_uri)

        // return redirect(url('/oauth/authorize?response_type=code&client_id='.$client->id.'&redirect_uri=' . $client->redirect));

        // return redirect('/test');
    }

    public function token(Request $request)
    {
        if ($request->method() === 'post') {
            return;
        }
        $response = Http::asForm()->post('http://identity.todoapp.local/oauth/token', [
            'grant_type' => 'authorization_code',
            'code' => $request->input('code'),
            'redirect_uri' => 'http://identity.todoapp.local/test/token',
            'client_id' => '5',
            'client_secret' => 'vGbbt7NLl6f7qd2ZSfsFfHOUBgyOtSyyexKOwJc7',
        ]);

        if ($response->successful()) {
            print 'aaaaa';
            $data = $response->json();
            print_r($data);
            $accessToken = $data['access_token'];
            print $accessToken;
        }
    }
}