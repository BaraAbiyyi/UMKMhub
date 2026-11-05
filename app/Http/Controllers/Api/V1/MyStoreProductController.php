<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\IndexProductRequest;
use App\Http\Requests\Product\StoreMyProductRequest;
use App\Http\Requests\Product\UpdateMyProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use App\Services\ProductService;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MyStoreProductController extends Controller
{
    public function __construct(
        private ProductService $service,
        private StoreService $storeService,
    ) {}

    public function index(IndexProductRequest $request, Store $store): AnonymousResourceCollection
    {
        $this->storeService->authorizeOwner($request->user(), $store);

        return ProductResource::collection($this->service->listForStore($store, $request->validated()));
    }

    public function store(StoreMyProductRequest $request, Store $store): JsonResponse
    {
        $this->storeService->authorizeOwner($request->user(), $store);

        $product = $this->service->createForStore($store, $request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('v1.products.show', $product));
    }

    public function update(UpdateMyProductRequest $request, Store $store, Product $product): ProductResource
    {
        $this->storeService->authorizeOwner($request->user(), $store);
        $this->service->ensureBelongsToStore($product, $store);

        return new ProductResource($this->service->update($product, $request->validated()));
    }

    public function destroy(Request $request, Store $store, Product $product): Response
    {
        $this->storeService->authorizeOwner($request->user(), $store);
        $this->service->ensureBelongsToStore($product, $store);

        $this->service->delete($product);

        return response()->noContent();
    }
}
