<?php

use App\Services\OrderService;
use Illuminate\Support\Facades\Schedule;

Schedule::call(fn () => app(OrderService::class)->expireOverdueOrders())
    ->everyMinute()
    ->name('expire-overdue-orders')
    ->withoutOverlapping();
