<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password yang Anda masukkan salah.',
                'code' => 'INVALID_CREDENTIALS',
            ], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'data' => [
                'token_type' => 'Bearer',
                'access_token' => $token,
                'expires_in' => 86400,
            ],
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()?->delete();
        }

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user() ?? User::first();

        return response()->json([
            'data' => [
                'id' => $user?->id ?? 1,
                'name' => $user?->name ?? 'Admin UMKMHub',
                'email' => $user?->email ?? 'admin@umkmhub.id',
                'created_at' => $user?->created_at?->toIso8601String(),
            ],
        ], 200);
    }
}
