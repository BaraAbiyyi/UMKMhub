<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class StockConflictException extends Exception
{
    public function __construct(
        string $message = 'Terjadi konflik versi stok.',
        private string $errorCode = 'STOK_VERSION_MISMATCH'
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'code' => $this->errorCode,
            'message' => $this->getMessage(),
        ], 409);
    }
}
