<?php

namespace App\Services;

use App\Exceptions\StockConflictException;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    /**
     * Katalog publik lintas toko: hanya produk aktif dari toko approved.
     */
    public function listPublic(array $filters): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? '-created_at';
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        return Product::query()
            ->publiclyVisible()
            ->with(['category', 'store', 'stock'])
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['store'] ?? null, fn ($q, $slug) => $q->whereHas('store', fn ($s) => $s->where('slug', $slug)))
            ->when($filters['search'] ?? $filters['q'] ?? null, function ($q, $term) {
                $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%"));
            })
            ->when(isset($filters['min_price']), fn ($q) => $q->where('price', '>=', $filters['min_price']))
            ->when(isset($filters['max_price']), fn ($q) => $q->where('price', '<=', $filters['max_price']))
            ->when(! empty($filters['in_stock']), fn ($q) => $q->whereHas('stock', fn ($s) => $s->where('quantity', '>', 0)))
            ->orderBy($column, $direction)
            ->orderBy('id')
            ->paginate((int) ($filters['per_page'] ?? 12))
            ->withQueryString();
    }

    public function listForStore(Store $store, array $filters): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? '-created_at';
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        return Product::query()
            ->where('store_id', $store->id)
            ->with(['category', 'stock'])
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->orderBy($column, $direction)
            ->orderBy('id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    public function createForStore(Store $store, array $data): Product
    {
        return DB::transaction(function () use ($store, $data) {
            $data['store_id'] = $store->id;

            if (isset($data['image'])) {
                $data['image_path'] = $data['image']->store('products', 'public');
                unset($data['image']);
            }

            $product = Product::create($data);
            Stock::create(['product_id' => $product->id, 'quantity' => 0, 'version' => 1]);

            return $product->load(['category', 'stock']);
        });
    }

    public function update(Product $product, array $data): Product
    {
        if (isset($data['image'])) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $data['image']->store('products', 'public');
            unset($data['image']);
        }

        $product->update($data);

        return $product->load(['category', 'stock']);
    }

    public function delete(Product $product): void
    {
        if ($product->orderItems()->exists()) {
            throw new StockConflictException(
                'Produk tidak dapat dihapus karena masih tercatat pada item pesanan.',
                'PRODUCT_HAS_RELATIONS',
            );
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();
    }

    /** Memastikan produk memang milik toko pada route. */
    public function ensureBelongsToStore(Product $product, Store $store): void
    {
        if ($product->store_id !== $store->id) {
            throw new StockConflictException('Produk bukan milik toko ini.', 'PRODUCT_NOT_IN_STORE', 404);
        }
    }
}
