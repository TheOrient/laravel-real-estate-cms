<?php

namespace App\Http\Controllers;

use App\Http\Requests\Front\Category\FilterCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Tüm ilanlar (/all) — slug olarak "tumu" kullanılır.
     */
    public function showAll(FilterCategoryRequest $request): View
    {
        $filters = $request->filters();
        $data = $this->categoryService->getCategoryPageData('tumu', $filters);

        return view('categories.show', $data);
    }

    /**
     * Kategori veya slug ile liste sayfası.
     */
    public function show(FilterCategoryRequest $request, string $slug): View|RedirectResponse
    {
        // `/all` is the canonical listing archive. Keep the legacy
        // `/category/tumu` URL from competing with it in search results.
        if ($slug === 'tumu') {
            return redirect()->route('categories.show.all', $request->query(), 301);
        }

        $filters = $request->filters();
        $data = $this->categoryService->getCategoryPageData($slug, $filters);

        return view('categories.show', $data);
    }
}
