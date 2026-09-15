<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the categories
     */
    public function index()
    {
        $categories = $this->categoryService->getAllCategories();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $categories = $this->categoryService->getAllCategoriesForSelection();
        $languages = $this->categoryService->getActiveLanguages();
        $descriptions = $this->categoryService->prepareEmptyDescriptions();

        return view('admin.categories.create', compact('categories', 'languages', 'descriptions'));
    }

    /**
     * Store a newly created category
     */
    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validatedWithDefaults();
        $this->categoryService->createCategory($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', __('admin/categories.created'));
    }

    /**
     * Show the form for editing the category
     */
    public function edit(Category $category)
    {
        $categories = $this->categoryService->getSelectableParents($category);
        $languages = $this->categoryService->getActiveLanguages();
        $descriptions = $this->categoryService->prepareCategoryDescriptions($category);

        return view('admin.categories.edit', compact('category', 'categories', 'languages', 'descriptions'));
    }

    /**
     * Update the category
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validatedWithDefaults();
        $this->categoryService->updateCategory($category, $validated);

        return redirect()->route('admin.categories.index')
            ->with('success', __('admin/categories.updated'));
    }

    /**
     * Remove the category
     */
    public function destroy(Category $category)
    {
        try {
            $this->categoryService->deleteCategory($category);

            return redirect()->route('admin.categories.index')
                ->with('success', __('admin/categories.deleted'));
        } catch (\Exception $e) {
            return redirect()->route('admin.categories.index')
                ->with('error', $e->getMessage());
        }
    }
}
