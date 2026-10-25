<?php

namespace App\Services;

use App\Exceptions\StockConflictException;
use App\Models\Stock;

class StockService
{
    public function updateStock(Stock $stock, array $data): Stock
    {
        if ($stock->version !== (int)$data['version']) {
            throw new StockConflictException("Stok telah diperbarui oleh pengguna lain. Silakan muat ulang data terbaru.");
        }

        $stock->update([
            'quantity' => $data['quantity'],
            'version'  => $stock->version + 1,
        ]);

        return $stock;
    }
}