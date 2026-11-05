<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Registrasi pengguna baru (Pembeli / Customer).
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::Customer,
        ]);

        $token = $user->createToken('auth-token', [$user->role->value])->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'role_label' => $user->role->label(),
                ],
                'token_type' => 'Bearer',
                'access_token' => $token,
                'expires_in' => 86400,
            ],
        ], 201);
    }

    /**
     * Login untuk semua peran (Superadmin, Admin UMKM, Customer).
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::with(['stores:id,owner_id,name,slug,status'])->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password yang Anda masukkan salah.',
                'code' => 'INVALID_CREDENTIALS',
            ], 401);
        }

        $token = $user->createToken('auth-token', [$user->role->value])->plainTextToken;

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'role_label' => $user->role->label(),
                    'stores' => $user->stores,
                ],
                'token_type' => 'Bearer',
                'access_token' => $token,
                'expires_in' => 86400,
            ],
        ], 200);
    }

    /**
     * Logout dan hapus token aktif.
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()?->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Profil pengguna yang sedang terautentikasi.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $user->load(['stores:id,owner_id,name,slug,status']);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'role_label' => $user->role->label(),
                'stores' => $user->stores,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
        ], 200);
    }
}
