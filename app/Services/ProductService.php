<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function list(array $filters): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? '-created_at';
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        return Product::query()
            ->with(['category', 'stock'])
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%"));
            })
            ->orderBy($column, $direction)
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($data);
            Stock::create(['product_id' => $product->id, 'quantity' => 0, 'version' => 1]);
            return $product->load(['category', 'stock']);
        });
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->load(['category', 'stock']);
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}