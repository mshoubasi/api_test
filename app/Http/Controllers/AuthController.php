<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
           'name'     => 'required|string',
           'email'    => 'required|email|unique:users',
           'password' => 'required|confirmed'
        ]);

        $data['password'] = bcrypt($request->password);

        $user = User::create($data);

        return response([
            'user' => $user,
            'token' => $user->createtoken('secret')
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if(!auth::attempt($data))
        {
            return response([
                'messsage' => 'invalid'
            ], 403);
        }

        return response([
            'user' => request()->user(),
            'token' => request()->user()->createtoken('secret')
        ],200);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response([
            'message' => 'Logout success.'
        ], 200);
    }

    public function user()
    {
        return response([
            'user' => auth()->user()
        ],200);
    }
}
