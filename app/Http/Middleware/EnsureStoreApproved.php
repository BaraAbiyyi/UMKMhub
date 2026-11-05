<?php

namespace App\Http\Middleware;

use App\Enums\StoreStatus;
use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'code' => 'UNAUTHENTICATED',
            ], 401);
        }

        // Ambil store dari route parameter {store} atau {store_id}
        $storeParam = $request->route('store') ?? $request->route('store_id');
        $store = null;

        if ($storeParam instanceof Store) {
            $store = $storeParam;
        } elseif (is_numeric($storeParam)) {
            $store = Store::find($storeParam);
        }

        // Jika tidak ada di route parameter, ambil toko pertama milik user
        if (! $store) {
            $store = $user->stores()->first();
        }

        if (! $store) {
            return response()->json([
                'message' => 'Toko tidak ditemukan atau belum didaftarkan.',
                'code' => 'STORE_NOT_FOUND',
            ], 404);
        }

        if ($store->status !== StoreStatus::Approved) {
            return response()->json([
                'message' => 'Toko Anda belum disetujui oleh Superadmin atau sedang disuspend.',
                'code' => 'STORE_NOT_APPROVED',
                'status' => $store->status->value,
                'rejection_reason' => $store->rejection_reason,
            ], 403);
        }

        return $next($request);
    }
}
