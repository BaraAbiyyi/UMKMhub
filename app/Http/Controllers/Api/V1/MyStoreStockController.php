<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\IndexStockRequest;
use App\Http\Requests\Stock\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Models\Stock;
use App\Models\Store;
use App\Services\StockService;
use App\Services\StoreService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MyStoreStockController extends Controller
{
    public function __construct(
        private StockService $service,
        private StoreService $storeService,
    ) {}

    public function index(IndexStockRequest $request, Store $store): AnonymousResourceCollection
    {
        $this->storeService->authorizeOwner($request->user(), $store);

        return StockResource::collection($this->service->listForStore($store, $request->validated()));
    }

    public function update(UpdateStockRequest $request, Store $store, Stock $stock): StockResource
    {
        $this->storeService->authorizeOwner($request->user(), $store);

        return new StockResource($this->service->updateStock($stock, $request->validated()));
    }
}
