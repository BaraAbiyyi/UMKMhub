<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Models\Stock;
use App\Services\StockService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockController extends Controller
{
    public function __construct(private StockService $service) {}

    public function index(): AnonymousResourceCollection
    {
        return StockResource::collection(Stock::with('product')->paginate(15));
    }

    public function update(UpdateStockRequest $request, Stock $stock): StockResource
    {
        $updated = $this->service->updateStock($stock, $request->validated());
        return new StockResource($updated);
    }
}