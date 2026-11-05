<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreApplicationController extends Controller
{
    public function __construct(private StoreService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:pending,approved,rejected,suspended'],
        ]);

        return StoreResource::collection($this->service->listApplications($validated));
    }
}
