<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService{
    public function createUser(User $user, array $validated){
        $user = $user->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'organizer'
        ]);
        
        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function loginUser(array $validated){
        $user = User::where('email', $validated['email'])->first();

        if(!$user|| !Hash::check($validated['password'], $user->password)){
            
        return response()->json([
                'message' => 'Invalid Credentials'
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }
}