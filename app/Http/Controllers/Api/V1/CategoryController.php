<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $service) {}

    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection($this->service->list());
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->service->create($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('v1.categories.show', $category));
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $updated = $this->service->update($category, $request->validated());

        return new CategoryResource($updated);
    }

    public function destroy(Category $category): Response
    {
        $this->service->delete($category);

        return response()->noContent();
    }
}
