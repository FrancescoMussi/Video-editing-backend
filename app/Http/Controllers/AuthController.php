<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\User;

class AuthController extends Controller
{
    public function login(Request $request) {

        // In these way we abstract sensitive informations such as client secret into the backend.
        // We don't have to insert it into the front-end app
        $username = $request->username;
        $password = $request->password;
        $request->request->add([
            'username' => $username,
            'password' => $password,
            'grant_type' => 'password',
            'client_id' => config('services.passport.client_id'),
            'client_secret' => config('services.passport.client_secret'),
            'scope' => '*'
        ]);

        $tokenRequest = Request::create(
            config('services.passport.login_endpoint'),
            'post'
        );
        $response = Route::dispatch($tokenRequest);

        return $response;
    }


    public function register(Request $request) {

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        return User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password), 
        ]);
    }

    public function admin_access() {

        $user = User::find(request('user_id'));

        if (Hash::check(request('password'), $user->password)) {
            return response('success', 200);
        } else {
            return response('denied', 401);
        }
    }

    public function logout() {
        
        auth()->user()->tokens->each(function($token, $key) {
            $token->delete();
        });

        return response('logged out successfully', 200);
    }
}
