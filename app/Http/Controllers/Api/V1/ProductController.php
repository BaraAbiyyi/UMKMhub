<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(private ProductService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $this->service->list($request->all());
        return ProductResource::collection($products);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load(['category', 'stock']));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->service->create($request->validated());
        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('v1.products.show', $product));
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $updated = $this->service->update($product, $request->validated());
        return new ProductResource($updated);
    }

    public function destroy(Product $product): Response
    {
        $this->service->delete($product);
        return response()->noContent();
    }
}