<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AppLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        /** @var User|null $user */
        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            AppLogger::log(
                'auth.login_failed',
                null,
                'user',
                $user?->id,
                ['email' => $validated['email']],
                $request
            );
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $token = $user->createToken('web')->plainTextToken;

        AppLogger::log(
            'auth.login',
            $user->id,
            'user',
            $user->id,
            ['email' => $user->email, 'role' => $user->role],
            $request
        );

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user?->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        AppLogger::log(
            'auth.logout',
            $user?->id,
            'user',
            $user?->id,
            [],
            $request
        );

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }
}


