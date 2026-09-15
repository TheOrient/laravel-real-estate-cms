<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of FAQs
     */
    public function index(Request $request)
    {
        $query = Faq::with('category')->ordered();

        // Filter by category
        if ($request->has('category') && $request->category !== '') {
            $query->where('faq_category_id', $request->category);
        }

        // Filter by active status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'LIKE', "%{$search}%")
                  ->orWhere('answer', 'LIKE', "%{$search}%");
            });
        }

        $faqs = $query->paginate(20);
        $categories = FaqCategory::active()->ordered()->get();

        return view('admin.faqs.index', compact('faqs', 'categories'));
    }

    /**
     * Show the form for creating a new FAQ
     */
    public function create()
    {
        $categories = FaqCategory::active()->ordered()->get();
        return view('admin.faqs.create', compact('categories'));
    }

    /**
     * Store a newly created FAQ
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'faq_category_id' => 'required|exists:faq_categories,id',
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        // Set default values
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Faq::create($validated);

        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ başarıyla oluşturuldu.');
    }

    /**
     * Show the form for editing the FAQ
     */
    public function edit(Faq $faq)
    {
        $categories = FaqCategory::active()->ordered()->get();
        return view('admin.faqs.edit', compact('faq', 'categories'));
    }

    /**
     * Update the FAQ
     */
    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'faq_category_id' => 'required|exists:faq_categories,id',
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        // Set default values
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $faq->update($validated);

        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ başarıyla güncellendi.');
    }

    /**
     * Remove the FAQ
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ başarıyla silindi.');
    }
}
