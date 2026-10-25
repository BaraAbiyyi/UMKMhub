<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class StockConflictException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'code'    => 'STOK_VERSION_MISMATCH',
            'message' => $this->getMessage() ?: 'Terjadi konflik versi stok.',
        ], 409);
    }
}