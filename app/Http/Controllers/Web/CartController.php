<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $service) {}

    public function index(Request $request): View
    {
        $cart = $this->service->getCart($request->user());

        return view('pages.cart', [
            'cart' => $cart,
            'groups' => $this->service->groupedSummary($cart),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'redirect' => ['nullable', 'string'],
        ]);

        $this->service->addItem($request->user(), (int) $validated['product_id'], (int) $validated['quantity']);

        if (! empty($validated['redirect'])) {
            return redirect($validated['redirect'])->with('status', 'Produk ditambahkan ke keranjang.');
        }

        return redirect()->route('cart.index')->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeItem($request, $cartItem);

        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:1000']]);

        $this->service->updateItem($cartItem, (int) $validated['quantity']);

        return redirect()->route('cart.index')->with('status', 'Jumlah diperbarui.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeItem($request, $cartItem);

        $this->service->removeItem($cartItem);

        return redirect()->route('cart.index')->with('status', 'Item dihapus dari keranjang.');
    }

    private function authorizeItem(Request $request, CartItem $item): void
    {
        abort_unless($item->cart->user_id === $request->user()->id, 403);
    }
}
