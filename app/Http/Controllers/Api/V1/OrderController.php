<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CheckoutRequest;
use App\Http\Requests\Order\SimulatePaymentRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $orders = $this->service->checkout($request->user(), (int) $request->validated()['address_id']);

        return OrderResource::collection($orders)->response()->setStatusCode(201);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()->orders()
            ->with(['store', 'items'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate((int) $request->query('per_page', 15))
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        $this->authorize('view', $order);

        return new OrderResource($order->load(['store', 'items', 'payment', 'user']));
    }

    public function cancel(Request $request, Order $order): OrderResource
    {
        $this->authorize('cancel', $order);

        return new OrderResource($this->service->cancel($order));
    }

    public function simulatePayment(SimulatePaymentRequest $request, Order $order): OrderResource
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return new OrderResource($this->service->simulatePayment($order, $request->validated()['result']));
    }
}
