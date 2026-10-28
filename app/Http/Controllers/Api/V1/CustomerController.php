<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function __construct(private CustomerService $service) {}

    public function index(): AnonymousResourceCollection
    {
        return CustomerResource::collection($this->service->list());
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->service->create($request->validated());

        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('v1.customers.show', $customer));
    }

    public function show(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }

    public function customerOrders(Request $request, Customer $customer): AnonymousResourceCollection
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $orders = $this->service->getOrders($customer, max($perPage, 1));

        return OrderResource::collection($orders);
    }
}
