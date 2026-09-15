<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display FAQ page
     */
    public function index(Request $request)
    {
        $selectedCategory = $request->get('category', 'all');

        // Get all active categories
        $categories = FaqCategory::active()->ordered()->get();

        // Get FAQs
        $faqsQuery = Faq::with('category')->active()->ordered();

        if ($selectedCategory !== 'all') {
            $faqsQuery->where('faq_category_id', $selectedCategory);
        }

        $faqs = $faqsQuery->get();

        // Group FAQs by category for display
        $faqsByCategory = $faqs->groupBy('faq_category_id');

        return view('faq.index', compact('categories', 'faqsByCategory', 'selectedCategory'));
    }
}
