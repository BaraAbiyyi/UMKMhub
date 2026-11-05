<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\OrderStatus;
use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class StatController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'stores' => [
                    'active' => Store::where('status', StoreStatus::Approved)->count(),
                    'pending' => Store::where('status', StoreStatus::Pending)->count(),
                    'total' => Store::count(),
                ],
                'products' => Product::count(),
                'users' => [
                    'total' => User::count(),
                    'customers' => User::where('role', UserRole::Customer)->count(),
                    'umkm_admins' => User::where('role', UserRole::UmkmAdmin)->count(),
                ],
                'orders' => [
                    'total' => Order::count(),
                    'paid' => Order::where('status', OrderStatus::Paid)->count(),
                    'completed' => Order::where('status', OrderStatus::Completed)->count(),
                ],
            ],
        ]);
    }
}
