<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->attempt($data)) {
            throw ValidationException::withMessages(['email' => ['Incorrect email or password.']]);
        }

        if (! Auth::user()->is_active) {
            Auth::guard('web')->logout();
            throw ValidationException::withMessages(['email' => ['This account is disabled.']]);
        }

        $request->session()->regenerate();

        return response()->json(['user' => Auth::user()->only(['id', 'name', 'email', 'role'])]);
    }

    public function me(Request $request)
    {
        if (! $request->user()->is_active) {
            abort(403, 'This account is disabled.');
        }
        return response()->json(['user' => $request->user()->only(['id', 'name', 'email', 'role'])]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Logged out.']);
    }
}
