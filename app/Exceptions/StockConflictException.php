<?php

namespace App\Exceptions;

class StockConflictException extends ApiException
{
    public function __construct(
        string $message = 'Terjadi konflik versi stok.',
        string $errorCode = 'STOK_VERSION_MISMATCH'
    ) {
        parent::__construct($message, $errorCode, 409);
    }
}
