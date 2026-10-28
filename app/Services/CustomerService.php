<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomerService
{
    public function list(): Collection
    {
        return Customer::all();
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function getOrders(Customer $customer, int $perPage = 15): LengthAwarePaginator
    {
        return $customer->orders()
            ->with(['items.product'])
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}
