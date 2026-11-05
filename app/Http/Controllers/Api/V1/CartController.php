<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $service) {}

    public function show(Request $request): JsonResponse
    {
        $cart = $this->service->getCart($request->user());

        return $this->payload($cart);
    }

    public function store(StoreCartItemRequest $request): JsonResponse
    {
        $cart = $this->service->addItem(
            $request->user(),
            (int) $request->validated()['product_id'],
            (int) $request->validated()['quantity'],
        );

        return $this->payload($cart)->setStatusCode(201);
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $this->authorizeItem($request, $cartItem);

        return $this->payload($this->service->updateItem($cartItem, (int) $request->validated()['quantity']));
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->authorizeItem($request, $cartItem);

        return $this->payload($this->service->removeItem($cartItem));
    }

    private function payload($cart): JsonResponse
    {
        $groups = $this->service->groupedSummary($cart);
        $grandTotal = array_sum(array_column($groups, 'total'));

        return response()->json([
            'data' => [
                'items' => CartItemResource::collection($cart->items),
                'groups' => $groups,
                'grand_total' => $grandTotal,
            ],
        ]);
    }

    private function authorizeItem(Request $request, CartItem $item): void
    {
        abort_unless($item->cart->user_id === $request->user()->id, 403, 'Item keranjang bukan milik Anda.');
    }
}
