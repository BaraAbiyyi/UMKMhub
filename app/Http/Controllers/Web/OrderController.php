<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function index(Request $request): View
    {
        return view('pages.orders.index', [
            'orders' => $request->user()->orders()
                ->with(['store', 'items'])
                ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
                ->latest('id')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return view('pages.orders.show', [
            'order' => $order->load(['store', 'items', 'payment']),
        ]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        try {
            $this->service->cancel($order);
        } catch (ApiException $e) {
            return redirect()->route('orders.show', $order)->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)->with('status', 'Pesanan dibatalkan.');
    }
}
