<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageDescription;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    /**
     * Display a listing of pages
     */
    public function index()
    {
        $pages = Page::orderBy('sort_order', 'asc')
                    ->orderBy('title', 'asc')
                    ->with(['descriptions', 'description'])
                    ->paginate(20);

        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new page
     */
    public function create()
    {
        $languages = Language::defaultListForForms();
        $descriptions = [];

        return view('admin.pages.create', compact('languages', 'descriptions'));
    }

    /**
     * Store a newly created page
     */
    public function store(Request $request)
    {
        $languages = Language::defaultListForForms();
        $defaultLanguage = $languages->firstWhere('is_default', true) ?? $languages->first();

        $rules = [
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'show_header' => 'boolean',
            'show_footer' => 'boolean',
        ];

        foreach ($languages as $language) {
            $required = $language->is_default ? 'required' : 'nullable';
            $rules["descriptions.{$language->id}.title"] = [$required, 'string', 'max:255'];
            $rules["descriptions.{$language->id}.slug"] = ['nullable', 'string', 'max:255', Rule::unique('page_descriptions', 'slug')];
            $rules["descriptions.{$language->id}.meta_description"] = ['nullable', 'string', 'max:500'];
            $rules["descriptions.{$language->id}.content"] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);

        $isActive = $request->has('is_active');
        $sortOrder = $validated['sort_order'] ?? 0;
        $showHeader = $request->has('show_header');
        $showFooter = $request->has('show_footer');

        $defaultTitle = $validated['descriptions'][$defaultLanguage->id]['title'] ?? null;
        if (!$defaultTitle) {
            return back()->withErrors(['descriptions.' . $defaultLanguage->id . '.title' => __('admin/pages.title_required_default')])->withInput();
        }

        $page = Page::create([
            'title' => $defaultTitle,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
            'show_header' => $showHeader,
            'show_footer' => $showFooter,
        ]);

        foreach ($languages as $language) {
            $desc = $validated['descriptions'][$language->id] ?? [];
            $title = $desc['title'] ?? null;
            $slug = $desc['slug'] ?? null;

            if (!$title && !$slug) {
                if ($language->is_default) {
                    $title = $defaultTitle;
                } else {
                    continue;
                }
            }

            if (!$slug && $title) {
                $slug = Str::slug($title);
            }

            PageDescription::updateOrCreate(
                [
                    'page_id' => $page->id,
                    'language_id' => $language->id,
                ],
                [
                    'title' => $title,
                    'slug' => $slug,
                    'meta_description' => $desc['meta_description'] ?? null,
                    'content' => $desc['content'] ?? null,
                ]
            );
        }

        return redirect()->route('admin.pages.index')
            ->with('success', __('admin/pages.created_successfully'));
    }

    /**
     * Display the specified page (redirect to frontend)
     */
    public function show(Page $page)
    {
        return redirect()->route('pages.show', $page->slug);
    }

    /**
     * Show the form for editing the page
     */
    public function edit(Page $page)
    {
        $languages = Language::defaultListForForms();
        $descriptions = $page->descriptions->keyBy('language_id')->map(function ($desc) {
            return [
                'title' => $desc->title,
                'slug' => $desc->slug,
                'meta_description' => $desc->meta_description,
                'content' => $desc->content,
            ];
        })->toArray();

        return view('admin.pages.edit', compact('page', 'languages', 'descriptions'));
    }

    /**
     * Update the specified page
     */
    public function update(Request $request, Page $page)
    {
        $languages = Language::defaultListForForms();
        $defaultLanguage = $languages->firstWhere('is_default', true) ?? $languages->first();
        $existingDescriptions = $page->descriptions->keyBy('language_id');

        $rules = [
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'show_header' => 'boolean',
            'show_footer' => 'boolean',
        ];

        foreach ($languages as $language) {
            $required = $language->is_default ? 'required' : 'nullable';
            $rules["descriptions.{$language->id}.title"] = [$required, 'string', 'max:255'];
            $rules["descriptions.{$language->id}.slug"] = [
                'nullable',
                'string',
                'max:255',
                Rule::unique('page_descriptions', 'slug')->ignore($existingDescriptions[$language->id]->id ?? null),
            ];
            $rules["descriptions.{$language->id}.meta_description"] = ['nullable', 'string', 'max:500'];
            $rules["descriptions.{$language->id}.content"] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);

        $isActive = $request->has('is_active');
        $sortOrder = $validated['sort_order'] ?? 0;
        $showHeader = $request->has('show_header');
        $showFooter = $request->has('show_footer');

        $defaultTitle = $validated['descriptions'][$defaultLanguage->id]['title'] ?? null;
        if (!$defaultTitle) {
            return back()->withErrors(['descriptions.' . $defaultLanguage->id . '.title' => __('admin/pages.title_required_default')])->withInput();
        }

        $page->update([
            'title' => $defaultTitle,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
            'show_header' => $showHeader,
            'show_footer' => $showFooter,
        ]);

        foreach ($languages as $language) {
            $desc = $validated['descriptions'][$language->id] ?? [];
            $title = $desc['title'] ?? null;
            $slug = $desc['slug'] ?? null;

            if (!$title && !$slug) {
                if ($language->is_default) {
                    $title = $defaultTitle;
                } else {
                    continue;
                }
            }

            if (!$slug && $title) {
                $slug = Str::slug($title);
            }

            PageDescription::updateOrCreate(
                [
                    'page_id' => $page->id,
                    'language_id' => $language->id,
                ],
                [
                    'title' => $title,
                    'slug' => $slug,
                    'meta_description' => $desc['meta_description'] ?? null,
                    'content' => $desc['content'] ?? null,
                ]
            );
        }

        return redirect()->route('admin.pages.index')
            ->with('success', __('admin/pages.updated_successfully'));
    }

    /**
     * Remove the specified page
     */
    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', __('admin/pages.deleted_successfully'));
    }
}
