<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'search' => ['sometimes', 'string', 'max:100'],
            'role' => ['sometimes', 'string', 'in:superadmin,umkm_admin,customer'],
        ]);

        $users = User::query()
            ->with('stores:id,owner_id,name,slug,status')
            ->when($validated['search'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")))
            ->when($validated['role'] ?? null, fn ($q, $role) => $q->where('role', $role))
            ->latest('id')
            ->paginate((int) $request->query('per_page', 15))
            ->withQueryString();

        return UserResource::collection($users);
    }
}
