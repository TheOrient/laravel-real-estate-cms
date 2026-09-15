<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AttributeService;
use Illuminate\Http\JsonResponse;

class AttributeController extends Controller
{
    protected $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    /**
     * Get all attributes for a category (including parent categories).
     */
    public function getCategoryAttributes(Category $category): JsonResponse
    {
        $attributes = $this->attributeService->getCategoryAttributes($category);
        return response()->json($attributes);
    }

    /**
     * Get filterable attributes for a category.
     */
    public function getFilterableAttributes(Category $category): JsonResponse
    {
        $attributes = $this->attributeService->getFilterableAttributes($category);
        return response()->json($attributes);
    }
}
