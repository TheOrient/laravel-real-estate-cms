<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Language;
use App\Services\BlogService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Admin CRUD for blog posts.
 *
 * Form layout mirrors AdminPageController: one tab per language for
 * the BlogDescription fields, plus the language-neutral metadata
 * (cover image, status, featured flag, publish date).
 */
class BlogController extends Controller
{
    public function __construct(
        protected BlogService $blogs,
        protected FileUploadService $files,
    ) {}

    public function index(Request $request)
    {
        $query = Blog::query()->with(['descriptions', 'author:id,first_name,last_name']);

        if ($status = $request->query('status')) {
            if (in_array($status, ['draft', 'published'], true)) {
                $query->where('status', $status);
            }
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->whereHas('descriptions', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $posts = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.blogs.index', compact('posts'));
    }

    public function create()
    {
        $languages = Language::defaultListForForms();
        $descriptions = []; // empty for create

        return view('admin.blogs.create', compact('languages', 'descriptions'));
    }

    public function store(Request $request)
    {
        $languages = Language::defaultListForForms();
        $defaultLanguage = $languages->firstWhere('is_default', true) ?? $languages->first();

        $validated = $this->validatePayload($request, $languages, $defaultLanguage);

        $imagePath = $this->handleImageUpload($request);

        $attributes = [
            'user_id'      => auth()->id(),
            'image'        => $imagePath,
            'status'       => $validated['status'] ?? 'draft',
            'is_active'    => $request->has('is_active'),
            'is_featured'  => $request->has('is_featured'),
            'published_at' => $validated['published_at'] ?? null,
        ];

        $descriptionsByLang = $this->extractDescriptions($validated, $languages, $defaultLanguage);

        $this->blogs->create($attributes, $descriptionsByLang);

        return redirect()->route('admin.blogs.index')
            ->with('success', __('admin/blog.created_successfully'));
    }

    public function edit(Blog $blog)
    {
        $languages = Language::defaultListForForms();
        $descriptions = $blog->descriptions->keyBy('language_id')->map(fn ($d) => [
            'title'            => $d->title,
            'slug'             => $d->slug,
            'excerpt'          => $d->excerpt,
            'body'             => $d->body,
            'meta_title'       => $d->meta_title,
            'meta_description' => $d->meta_description,
            'meta_keywords'    => $d->meta_keywords,
        ])->toArray();

        return view('admin.blogs.edit', compact('blog', 'languages', 'descriptions'));
    }

    public function update(Request $request, Blog $blog)
    {
        $languages = Language::defaultListForForms();
        $defaultLanguage = $languages->firstWhere('is_default', true) ?? $languages->first();

        $validated = $this->validatePayload($request, $languages, $defaultLanguage, $blog);

        $attributes = [
            'status'       => $validated['status'] ?? $blog->status,
            'is_active'    => $request->has('is_active'),
            'is_featured'  => $request->has('is_featured'),
            'published_at' => $validated['published_at'] ?? $blog->published_at,
        ];

        if ($request->hasFile('image')) {
            // Remove the old file if it exists locally; keep the field
            // unchanged on upload failure.
            $newPath = $this->handleImageUpload($request);
            if ($newPath) {
                $this->deleteImageIfExists($blog->image);
                $attributes['image'] = $newPath;
            }
        }

        $descriptionsByLang = $this->extractDescriptions($validated, $languages, $defaultLanguage);

        $this->blogs->update($blog, $attributes, $descriptionsByLang);

        return redirect()->route('admin.blogs.index')
            ->with('success', __('admin/blog.updated_successfully'));
    }

    public function destroy(Blog $blog)
    {
        $this->deleteImageIfExists($blog->image);
        $this->blogs->delete($blog);

        return redirect()->route('admin.blogs.index')
            ->with('success', __('admin/blog.deleted_successfully'));
    }

    /* ----------------------- helpers ----------------------- */

    /**
     * Build validation rules: one set per language description,
     * plus the top-level fields. The default-language title is the
     * only field marked required so an editor can save a draft in
     * one language and translate later.
     */
    protected function validatePayload(Request $request, $languages, $defaultLanguage, ?Blog $existing = null): array
    {
        $rules = [
            'status'       => ['nullable', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'is_active'    => ['nullable', 'boolean'],
            'is_featured'  => ['nullable', 'boolean'],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'], // 4MB
        ];

        foreach ($languages as $language) {
            $isDefault = $language->is_default;
            $required = $isDefault ? 'required' : 'nullable';

            $rules["descriptions.{$language->id}.title"]   = [$required, 'string', 'max:255'];
            $rules["descriptions.{$language->id}.slug"]    = [
                'nullable', 'string', 'max:255',
                Rule::unique('blog_descriptions', 'slug')
                    ->where(fn ($q) => $q->where('language_id', $language->id))
                    ->ignore(
                        optional($existing?->descriptions->firstWhere('language_id', $language->id))->id
                    ),
            ];
            $rules["descriptions.{$language->id}.excerpt"]          = ['nullable', 'string', 'max:1000'];
            $rules["descriptions.{$language->id}.body"]             = ['nullable', 'string'];
            $rules["descriptions.{$language->id}.meta_title"]       = ['nullable', 'string', 'max:255'];
            $rules["descriptions.{$language->id}.meta_description"] = ['nullable', 'string', 'max:500'];
            $rules["descriptions.{$language->id}.meta_keywords"]    = ['nullable', 'string', 'max:255'];
        }

        return $request->validate($rules);
    }

    protected function extractDescriptions(array $validated, $languages, $defaultLanguage): array
    {
        $byLang = [];
        $defaultTitle = $validated['descriptions'][$defaultLanguage->id]['title'] ?? null;

        foreach ($languages as $language) {
            $desc = $validated['descriptions'][$language->id] ?? [];

            // For the default language we always carry a title — falls
            // back to itself so the unique-slug logic in the service has
            // something to slug from.
            if ($language->is_default && empty($desc['title']) && $defaultTitle) {
                $desc['title'] = $defaultTitle;
            }

            $byLang[$language->id] = $desc;
        }

        return $byLang;
    }

    /**
     * Save the uploaded cover image into public/uploads/blogs/{YYYY}/{MM}/.
     * Returns the relative path (suitable for the Glide image route)
     * or null when no upload happened.
     */
    protected function handleImageUpload(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $subdir = 'uploads/blogs/' . now()->format('Y/m');
        $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());

        $absolute = public_path($subdir);
        if (! is_dir($absolute)) {
            @mkdir($absolute, 0755, true);
        }

        $file->move($absolute, $filename);

        return $subdir . '/' . $filename;
    }

    protected function deleteImageIfExists(?string $relativePath): void
    {
        if (! $relativePath) return;
        $full = public_path($relativePath);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
