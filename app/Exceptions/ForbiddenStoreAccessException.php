<?php

namespace App\Exceptions;

class ForbiddenStoreAccessException extends ApiException
{
    public function __construct(string $message = 'Anda tidak memiliki akses ke toko ini.')
    {
        parent::__construct($message, 'FORBIDDEN_STORE_ACCESS', 403);
    }
}
