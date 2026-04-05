<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if(Auth::attempt($credentials)){
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message'=>'Login berhasil',
                'user'=>$user,
                'token'=>$token
            ]);
        }

        return response()->json([
            'message'=>'Email atau password salah'
        ],401);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        return response()->json([
            'message'=>'Logout berhasil'
        ]);
    }
}