<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;

class AuthController extends Controller
{
    public function register(Request $request, User $user, UserService $userService)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required','string', 'min:8', 'confirmed']
        ]);

        $user = $userService->createUser($user, $validated);

        return response()->json([
            'Message' => 'Registration successful',
            'user' => $user['user'],
            'token' => $user['token']
        ], 201);
    }

    public function login(Request $request, UserService $userService){

    $validated = $request->validate([
            'password' => ['required', 'string'],
            'email' => ['required', 'email']
    ]);

        $user = $userService->loginUser($validated);
    
        return response()->json([
            'message' => 'Login Successful',
            'user' => $user['user'],
            'token' => $user['token']
        ]);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logout successful'
            ]);
            
    }
}
