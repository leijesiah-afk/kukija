<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'address' => ['required', 'string', 'max:2000'],
            'contact_no' => ['required', 'string', 'max:30', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $name = trim(($validated['first_name'] ?? '').' '.($validated['last_name'] ?? ''));

        $user = User::query()->create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $name,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'address' => $validated['address'],
            'contact_no' => $validated['contact_no'],
            'role' => 'customer',
            'status' => 'active',
        ]);

        $tokenName = $validated['device_name'] ?? 'storefront';
        $token = $user->createToken($tokenName, ['customer']);

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var User|null $user */
        $user = User::query()
            ->where('email', $validated['email'])
            ->where('role', 'customer')
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 422);
        }

        if (($user->status ?? 'active') !== 'active') {
            return response()->json([
                'message' => 'Account is not active.',
            ], 403);
        }

        $tokenName = $validated['device_name'] ?? 'storefront';
        $token = $user->createToken($tokenName, ['customer']);

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $user,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $request->user()?->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }
}
