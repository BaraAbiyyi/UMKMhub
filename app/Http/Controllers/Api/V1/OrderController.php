<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->service->checkout($request->validated());

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('v1.orders.show', $order));
    }

    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load(['customer', 'items.product']));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): OrderResource
    {
        $updated = $this->service->updateStatus($order, $request->validated()['status']);

        return new OrderResource($updated);
    }
}
