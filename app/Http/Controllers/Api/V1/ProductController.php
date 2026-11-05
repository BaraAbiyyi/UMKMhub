<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\IndexProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(private ProductService $service) {}

    public function index(IndexProductRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection($this->service->listPublic($request->validated()));
    }

    public function show(Product $product): ProductResource
    {
        abort_unless($product->is_active && $product->store?->isApproved(), 404);

        return new ProductResource($product->load(['category', 'store', 'stock']));
    }
}
