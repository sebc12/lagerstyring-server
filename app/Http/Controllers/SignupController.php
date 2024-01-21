<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SignupController extends Controller
{
    public function index(Request $request)
    {

        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'LocationID' => 'required|numeric',
        ]);

        $userData = $request->only(['email', 'password', 'LocationID']);

        $user = User::create($userData);

        if ($user) {
            return response()->json(['message' => 'Signed up successfully'], 201);
        } else {
            return response()->json(['message' => 'Failed to signup'], 500);
        }
    }
}
