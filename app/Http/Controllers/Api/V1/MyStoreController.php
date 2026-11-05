<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MyStoreController extends Controller
{
    public function __construct(private StoreService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return StoreResource::collection(
            $request->user()->stores()->with('category')->withCount('products')->get()
        );
    }

    public function show(Request $request, Store $store): StoreResource
    {
        $this->service->authorizeOwner($request->user(), $store);

        return new StoreResource($store->load('category')->loadCount('products'));
    }

    public function update(UpdateStoreRequest $request, Store $store): StoreResource
    {
        $this->service->authorizeOwner($request->user(), $store);

        return new StoreResource($this->service->updateProfile($store, $request->validated()));
    }

    public function stats(Request $request, Store $store): JsonResponse
    {
        $this->service->authorizeOwner($request->user(), $store);

        $period = $request->query('period', 'today');
        $from = match ($period) {
            '7d' => now()->subDays(6)->startOfDay(),
            'month' => now()->startOfMonth(),
            default => now()->startOfDay(),
        };

        $orders = $store->orders()->where('created_at', '>=', $from);

        $series = (clone $orders)
            ->get(['created_at', 'total', 'status'])
            ->groupBy(fn ($o) => $o->created_at->format('Y-m-d'))
            ->map(fn ($group, $date) => [
                'date' => $date,
                'sales' => (int) $group->whereIn('status', ['paid', 'processing', 'shipped', 'completed'])->sum('total'),
                'orders' => $group->count(),
            ])->values();

        return response()->json([
            'data' => [
                'period' => $period,
                'total_sales' => (int) (clone $orders)->whereIn('status', ['paid', 'processing', 'shipped', 'completed'])->sum('total'),
                'new_orders' => (clone $orders)->where('status', 'paid')->count(),
                'active_products' => $store->products()->where('is_active', true)->count(),
                'low_stock' => $store->products()->whereHas('stock', fn ($q) => $q->where('quantity', '<=', (int) config('umkmhub.low_stock_threshold')))->count(),
                'series' => $series,
            ],
        ]);
    }
}
