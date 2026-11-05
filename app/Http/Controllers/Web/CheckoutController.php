<?php

namespace App\Http\Controllers\Web;

use App\Enums\OrderStatus;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $cart = $this->cartService->getCart($request->user());

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        return view('pages.checkout.index', [
            'cart' => $cart,
            'groups' => $this->cartService->groupedSummary($cart),
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->latest('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(['address_id' => ['required', 'integer', 'exists:addresses,id']]);

        try {
            $orders = $this->orderService->checkout($request->user(), (int) $validated['address_id']);
        } catch (ApiException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        $first = $orders->first();
        $pending = $orders->where('status', OrderStatus::PendingPayment);

        $message = $pending->count() > 1
            ? 'Pesanan dibuat untuk '.$pending->count().' toko. Selesaikan pembayaran simulasi setiap pesanan.'
            : 'Pesanan dibuat. Silakan selesaikan pembayaran simulasi.';

        return redirect()->route('checkout.payment', $first)->with('status', $message);
    }

    public function payment(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return view('pages.checkout.payment', [
            'order' => $order->load(['store', 'items', 'payment']),
            'pendingOrders' => $request->user()->orders()
                ->where('status', OrderStatus::PendingPayment)
                ->with('store')
                ->get(),
        ]);
    }

    public function pay(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $validated = $request->validate(['result' => ['required', 'in:success,failed,expired']]);

        try {
            $this->orderService->simulatePayment($order, $validated['result']);
        } catch (ApiException $e) {
            return redirect()->route('orders.show', $order)->with('error', $e->getMessage());
        }

        $status = $validated['result'] === 'success'
            ? 'Pembayaran simulasi berhasil.'
            : 'Pembayaran simulasi gagal; pesanan dibatalkan dan stok dikembalikan.';

        return redirect()->route('checkout.done', $order)->with('status', $status);
    }

    public function done(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return view('pages.checkout.done', [
            'order' => $order->load(['store', 'items', 'payment']),
        ]);
    }
}
