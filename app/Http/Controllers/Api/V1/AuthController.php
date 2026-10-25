<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Bearer token generated successfully',
            'token' => 'demo_bearer_token_umkmhub_12345'
        ]);
    }

    public function logout(): JsonResponse
    {
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => ['id' => 1, 'name' => 'Admin UMKMHub', 'email' => 'admin@umkmhub.id']]);
    }
}