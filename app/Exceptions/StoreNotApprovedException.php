<?php

namespace App\Exceptions;

class StoreNotApprovedException extends ApiException
{
    public function __construct(string $message = 'Toko belum disetujui atau sedang disuspend.')
    {
        parent::__construct($message, 'STORE_NOT_APPROVED', 403);
    }
}
