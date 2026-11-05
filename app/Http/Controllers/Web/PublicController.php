<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Services\ProductService;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function __construct(
        private StoreService $storeService,
        private ProductService $productService,
    ) {}

    public function home(): View
    {
        return view('pages.landing', [
            'stores' => Store::where('status', 'approved')->with('category')->latest('id')->take(3)->get(),
            'products' => Product::publiclyVisible()->with(['store', 'category', 'stock'])->latest('id')->take(4)->get(),
            'categories' => Category::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function stores(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer'],
        ]);

        return view('pages.stores.index', [
            'stores' => $this->storeService->listPublic($filters),
            'categories' => Category::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function store(Store $store): View
    {
        abort_unless($store->isApproved(), 404);

        return view('pages.stores.show', [
            'store' => $store->load('category')->loadCount('products'),
            'products' => $this->productService->listForStore($store, ['is_active' => true, 'per_page' => 100]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function products(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer'],
            'store' => ['nullable', 'string', 'max:150'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'in_stock' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'string', 'in:price,-price,name,-name,-created_at'],
        ]);

        return view('pages.products.index', [
            'products' => $this->productService->listPublic($filters),
            'categories' => Category::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function product(Product $product): View
    {
        abort_unless($product->is_active && $product->store?->isApproved(), 404);

        return view('pages.products.show', [
            'product' => $product->load(['category', 'store', 'stock']),
            'related' => Product::publiclyVisible()
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)
                ->with(['store', 'stock'])
                ->take(4)
                ->get(),
        ]);
    }
}
