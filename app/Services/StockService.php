<?php

namespace App\Services;

use App\Exceptions\StockConflictException;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StockService
{
    public function listForStore(Store $store, array $filters = []): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? 'quantity';
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        return Stock::query()
            ->whereHas('product', fn ($q) => $q->where('store_id', $store->id))
            ->with('product.category')
            ->when(! empty($filters['low_stock']), fn ($q) => $q->where('quantity', '<=', (int) config('umkmhub.low_stock_threshold')))
            ->orderBy($column, $direction)
            ->orderBy('id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    public function updateStock(Stock $stock, array $data): Stock
    {
        if ($stock->version !== (int) $data['version']) {
            throw new StockConflictException(
                'Stok telah diperbarui oleh pengguna lain. Silakan muat ulang data terbaru.',
                'STOK_VERSION_MISMATCH',
            );
        }

        $stock->update([
            'quantity' => $data['quantity'],
            'version' => $stock->version + 1,
        ]);

        return $stock->load('product.category');
    }
}
