<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\IndexStockRequest;
use App\Http\Requests\Stock\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Models\Stock;
use App\Services\StockService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockController extends Controller
{
    public function __construct(private StockService $service) {}

    public function index(IndexStockRequest $request): AnonymousResourceCollection
    {
        $stocks = $this->service->list($request->validated());

        return StockResource::collection($stocks);
    }

    public function update(UpdateStockRequest $request, Stock $stock): StockResource
    {
        $updated = $this->service->updateStock($stock, $request->validated());

        return new StockResource($updated);
    }
}
