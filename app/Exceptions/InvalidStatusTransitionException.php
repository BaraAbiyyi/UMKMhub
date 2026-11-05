<?php

namespace App\Exceptions;

use App\Enums\OrderStatus;

class InvalidStatusTransitionException extends ApiException
{
    public function __construct(OrderStatus $from, OrderStatus $to)
    {
        parent::__construct(
            "Transisi status dari {$from->value} ke {$to->value} tidak diizinkan.",
            'INVALID_STATUS_TRANSITION',
            422,
        );
    }
}
