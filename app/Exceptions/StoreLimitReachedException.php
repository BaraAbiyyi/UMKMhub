<?php

namespace App\Exceptions;

class StoreLimitReachedException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'Satu akun hanya boleh memiliki maksimal '.config('umkmhub.max_stores_per_owner').' toko.',
            'STORE_LIMIT_REACHED',
            422,
        );
    }
}
