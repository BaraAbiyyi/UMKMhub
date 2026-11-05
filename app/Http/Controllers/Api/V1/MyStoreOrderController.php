<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Store;
use App\Services\OrderService;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class MyStoreOrderController extends Controller
{
    public function __construct(
        private OrderService $service,
        private StoreService $storeService,
    ) {}

    public function index(Request $request, Store $store): AnonymousResourceCollection
    {
        $this->storeService->authorizeOwner($request->user(), $store);

        $validated = $request->validate([
            'status' => ['sometimes', Rule::enum(OrderStatus::class)],
        ]);

        $orders = $store->orders()
            ->with(['items', 'user'])
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate((int) $request->query('per_page', 15))
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    public function updateStatus(Request $request, Store $store, Order $order): OrderResource
    {
        $this->storeService->authorizeOwner($request->user(), $store);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        return new OrderResource(
            $this->service->updateStatus($order, OrderStatus::from($validated['status']))
        );
    }
}
