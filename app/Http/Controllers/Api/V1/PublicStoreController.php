<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\IndexProductRequest;
use App\Http\Requests\Store\IndexStoreRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Services\ProductService;
use App\Services\StoreService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicStoreController extends Controller
{
    public function __construct(
        private StoreService $storeService,
        private ProductService $productService,
    ) {}

    public function index(IndexStoreRequest $request): AnonymousResourceCollection
    {
        return StoreResource::collection($this->storeService->listPublic($request->validated()));
    }

    public function show(Store $store): StoreResource
    {
        abort_unless($store->isApproved(), 404);

        return new StoreResource($store->load('category')->loadCount('products'));
    }

    public function products(IndexProductRequest $request, Store $store): AnonymousResourceCollection
    {
        abort_unless($store->isApproved(), 404);

        $filters = $request->validated();
        $filters['is_active'] = true;

        return ProductResource::collection($this->productService->listForStore($store, $filters));
    }
}
