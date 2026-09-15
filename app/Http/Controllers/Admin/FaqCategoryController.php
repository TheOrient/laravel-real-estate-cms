<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqCategoryController extends Controller
{
    /**
     * Display a listing of FAQ categories
     */
    public function index()
    {
        $categories = FaqCategory::ordered()->paginate(20);
        return view('admin.faq-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new FAQ category
     */
    public function create()
    {
        return view('admin.faq-categories.create');
    }

    /**
     * Store a newly created FAQ category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:faq_categories',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set default values
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        FaqCategory::create($validated);

        return redirect()->route('admin.faq-categories.index')
            ->with('success', 'FAQ kategorisi başarıyla oluşturuldu.');
    }

    /**
     * Show the form for editing the FAQ category
     */
    public function edit(FaqCategory $faqCategory)
    {
        return view('admin.faq-categories.edit', compact('faqCategory'));
    }

    /**
     * Update the FAQ category
     */
    public function update(Request $request, FaqCategory $faqCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:faq_categories,slug,' . $faqCategory->id,
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set default values
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $faqCategory->update($validated);

        return redirect()->route('admin.faq-categories.index')
            ->with('success', 'FAQ kategorisi başarıyla güncellendi.');
    }

    /**
     * Remove the FAQ category
     */
    public function destroy(FaqCategory $faqCategory)
    {
        // Check if category has FAQs
        if ($faqCategory->faqs()->count() > 0) {
            return redirect()->route('admin.faq-categories.index')
                ->with('error', 'Bu kategoriye ait FAQ\'lar bulunduğu için silinemez. Önce FAQ\'ları silin veya başka bir kategoriye taşıyın.');
        }

        $faqCategory->delete();

        return redirect()->route('admin.faq-categories.index')
            ->with('success', 'FAQ kategorisi başarıyla silindi.');
    }
}
