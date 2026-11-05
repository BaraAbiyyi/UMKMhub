<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplication\StoreStoreApplicationRequest;
use App\Http\Resources\StoreResource;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;

class StoreApplicationController extends Controller
{
    public function __construct(private StoreService $service) {}

    public function store(StoreStoreApplicationRequest $request): JsonResponse
    {
        $store = $this->service->register($request->validated());

        return (new StoreResource($store))
            ->response()
            ->setStatusCode(201);
    }
}
