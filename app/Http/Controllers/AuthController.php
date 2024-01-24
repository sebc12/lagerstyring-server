<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:8|',
            'LocationID' => 'required|numeric',
        ]);

        $user = User::create([
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'LocationID' => $validatedData['LocationID']
        ]);

        return response(['user' => $user, 'message' => 'Registration successful']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = request(['email', 'password']);

        if (Auth::attempt($credentials)) {
            $user = $request->user();
            $token = $user->createToken('token')->plainTextToken;

            return response(['user' => $user, 'token' => $token]);
        }

        return response()->json(['message' => 'The provided credentials are incorrect.'], 422);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response(['message' => 'Logout successful']);
    }
}
