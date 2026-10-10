<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Ports\CategoryReader;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PaginatedResource;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryReader $categoryReader,
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->categoryReader->all();

        return response()->json([
            'items' => array_map(fn ($c) => new CategoryResource((object)$c), $categories),
            'page' => 1,
            'size' => count($categories),
            'total' => count($categories),
        ]);
    }
}