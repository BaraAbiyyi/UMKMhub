<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\StoreStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectStoreRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreController extends Controller
{
    public function __construct(private StoreService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:pending,approved,rejected,suspended'],
            'search' => ['sometimes', 'string', 'max:100'],
        ]);

        return StoreResource::collection($this->service->listApplications($validated));
    }

    public function approve(Request $request, Store $store): StoreResource
    {
        return new StoreResource($this->service->approve($store, $request->user()));
    }

    public function reject(RejectStoreRequest $request, Store $store): StoreResource
    {
        return new StoreResource(
            $this->service->reject($store, $request->user(), $request->validated()['rejection_reason'])
        );
    }

    public function suspend(Store $store): StoreResource
    {
        return new StoreResource($this->service->setStatus($store, StoreStatus::Suspended));
    }

    public function activate(Store $store): StoreResource
    {
        return new StoreResource($this->service->setStatus($store, StoreStatus::Approved));
    }
}
