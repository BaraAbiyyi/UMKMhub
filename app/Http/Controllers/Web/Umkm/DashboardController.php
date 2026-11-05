<?php

namespace App\Http\Controllers\Web\Umkm;

use App\Enums\OrderStatus;
use App\Enums\StoreStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Exceptions\StockConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreMyProductRequest;
use App\Http\Requests\Product\UpdateMyProductRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use App\Services\OrderService;
use App\Services\ProductService;
use App\Services\StockService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private StoreService $storeService,
        private ProductService $productService,
        private StockService $stockService,
        private OrderService $orderService,
    ) {}

    public function index(Request $request): View
    {
        $store = $this->activeStore($request);

        if (! $store->isApproved()) {
            return view('dashboard.umkm.status', ['store' => $store]);
        }

        $today = now()->startOfDay();
        $orders = $store->orders()->where('created_at', '>=', $today);

        return view('dashboard.umkm.index', [
            'store' => $store,
            'stats' => [
                'sales' => (int) $store->orders()->whereIn('status', ['paid', 'processing', 'shipped', 'completed'])->where('created_at', '>=', $today)->sum('total'),
                'new_orders' => (clone $orders)->where('status', OrderStatus::Paid)->count(),
                'active_products' => $store->products()->where('is_active', true)->count(),
                'low_stock' => $store->products()->whereHas('stock', fn ($q) => $q->where('quantity', '<=', (int) config('umkmhub.low_stock_threshold')))->count(),
            ],
            'latestOrders' => $store->orders()->with(['user', 'items'])->latest('id')->take(5)->get(),
            'series' => $this->series($store),
        ]);
    }

    public function products(Request $request): View
    {
        $store = $this->activeStore($request);
        $this->guardApproved($store);

        return view('dashboard.umkm.products', [
            'store' => $store,
            'products' => $this->productService->listForStore($store, $request->only('search', 'category_id', 'is_active')),
            'categories' => Category::orderBy('name')->get(),
            'filters' => $request->only('search', 'category_id', 'is_active'),
        ]);
    }

    public function storeProduct(StoreMyProductRequest $request): RedirectResponse
    {
        $store = $this->activeStore($request);
        $this->guardApproved($store);

        $this->productService->createForStore($store, $request->validated());

        return redirect()->route('umkm.products')->with('status', 'Produk ditambahkan.');
    }

    public function updateProduct(UpdateMyProductRequest $request, Product $product): RedirectResponse
    {
        $store = $this->activeStore($request);
        $this->guardApproved($store);
        $this->productService->ensureBelongsToStore($product, $store);

        $this->productService->update($product, $request->validated());

        return redirect()->route('umkm.products')->with('status', 'Produk diperbarui.');
    }

    public function destroyProduct(Request $request, Product $product): RedirectResponse
    {
        $store = $this->activeStore($request);
        $this->productService->ensureBelongsToStore($product, $store);

        $this->productService->delete($product);

        return redirect()->route('umkm.products')->with('status', 'Produk dihapus.');
    }

    public function stocks(Request $request): View
    {
        $store = $this->activeStore($request);
        $this->guardApproved($store);

        return view('dashboard.umkm.stocks', [
            'store' => $store,
            'stocks' => $this->stockService->listForStore($store, $request->only('low_stock')),
            'filters' => $request->only('low_stock'),
        ]);
    }

    public function updateStock(Request $request, Stock $stock): RedirectResponse
    {
        $store = $this->activeStore($request);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
            'version' => ['required', 'integer'],
        ]);

        try {
            $this->stockService->updateStock($stock, $validated);

            return redirect()->route('umkm.stocks')->with('status', 'Stok diperbarui.');
        } catch (StockConflictException $e) {
            return redirect()->route('umkm.stocks')
                ->with('error', 'Stok ini baru saja diubah pihak lain. Muat ulang lalu coba lagi.')
                ->with('conflict_stock_id', $stock->id);
        }
    }

    public function orders(Request $request): View
    {
        $store = $this->activeStore($request);
        $this->guardApproved($store);

        $status = $request->query('status');

        return view('dashboard.umkm.orders', [
            'store' => $store,
            'orders' => $store->orders()
                ->with(['user', 'items'])
                ->when($status, fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate(10)
                ->withQueryString(),
            'status' => $status,
        ]);
    }

    public function orderShow(Request $request, Order $order): View
    {
        $store = $this->activeStore($request);
        abort_unless($order->store_id === $store->id, 403);

        return view('dashboard.umkm.order-show', [
            'store' => $store,
            'order' => $order->load(['user', 'items', 'payment']),
        ]);
    }

    public function updateOrderStatus(Request $request, Order $order): RedirectResponse
    {
        $store = $this->activeStore($request);
        abort_unless($order->store_id === $store->id, 403);

        $validated = $request->validate(['status' => ['required', 'string']]);

        try {
            $this->orderService->updateStatus($order, OrderStatus::from($validated['status']));

            return redirect()->route('umkm.orders.show', $order)->with('status', 'Status pesanan diperbarui.');
        } catch (InvalidStatusTransitionException $e) {
            return redirect()->route('umkm.orders.show', $order)->with('error', $e->getMessage());
        }
    }

    public function storeProfile(Request $request): View
    {
        $store = $this->activeStore($request);

        return view('dashboard.umkm.store', [
            'store' => $store->load('category'),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function updateStore(UpdateStoreRequest $request): RedirectResponse
    {
        $store = $this->activeStore($request);

        $this->storeService->updateProfile($store, $request->validated());

        return redirect()->route('umkm.store')->with('status', 'Profil toko diperbarui.');
    }

    public function reapply(Request $request): RedirectResponse
    {
        $store = $this->activeStore($request);
        abort_unless($store->status === StoreStatus::Rejected, 422);

        $this->storeService->setStatus($store, StoreStatus::Pending);
        $store->update(['submitted_at' => now(), 'rejection_reason' => null]);

        return redirect()->route('umkm.dashboard')->with('status', 'Pendaftaran ulang dikirim ke Superadmin.');
    }

    private function activeStore(Request $request): Store
    {
        $stores = $request->user()->stores()->with('category')->get();

        $store = $request->query('store')
            ? $stores->firstWhere('id', (int) $request->query('store'))
            : $stores->firstWhere('id', (int) $request->session()->get('active_store_id'));

        $store ??= $stores->first();

        abort_unless($store, 404, 'Anda belum memiliki toko.');

        $request->session()->put('active_store_id', $store->id);

        return $store;
    }

    private function guardApproved(Store $store): void
    {
        abort_unless($store->isApproved(), 403, 'Toko Anda belum disetujui.');
    }

    /**
     * @return array{labels: array<int, string>, sales: array<int, int>, orders: array<int, int>}
     */
    private function series(Store $store): array
    {
        $start = now()->subDays(6)->startOfDay();
        $rows = $store->orders()->where('created_at', '>=', $start)->get(['created_at', 'total', 'status']);

        $labels = [];
        $sales = [];
        $counts = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $labels[] = $date->format('d M');
            $dayRows = $rows->filter(fn ($o) => $o->created_at->isSameDay($date));
            $sales[] = (int) $dayRows->whereIn('status', ['paid', 'processing', 'shipped', 'completed'])->sum('total');
            $counts[] = $dayRows->count();
        }

        return ['labels' => $labels, 'sales' => $sales, 'orders' => $counts];
    }
}
