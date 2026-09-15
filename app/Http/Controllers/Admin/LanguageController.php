<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Language\StoreLanguageRequest;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class LanguageController extends Controller
{
    /**
     * Display a listing of the languages.
     */
    public function index()
    {
        $languages = Language::ordered()->paginate(15);

        return view('admin.languages.index', compact('languages'));
    }

    /**
     * Show the form for creating a new language.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created language.
     */
    public function store(StoreLanguageRequest $request)
    {
        abort(404);
    }

    /**
     * Display the specified language (redirect to edit page).
     */
    public function show(Language $language)
    {
        return redirect()->route('admin.languages.edit', $language);
    }

    /**
     * Show the form for editing the language.
     */
    public function edit(Language $language)
    {
        return view('admin.languages.edit', compact('language'));
    }

    /**
     * Update the specified language.
     */
    public function update(Request $request, Language $language)
    {
        // Languages are seeded from config/app.php and are not edited
        // through the admin UI in this build — the listing covers the
        // two supported locales (TR + EN). To add a new language, add
        // a translations file under lang/<code>/ and update the
        // LanguageSeeder, then re-seed.
        return redirect()
            ->route('admin.languages.edit', $language)
            ->with('warning', __('admin/languages.edit_disabled_seeded'));
    }

    /**
     * Remove the specified language.
     */
    public function destroy(Language $language)
    {
        $this->deleteIconFiles($language->id);
        $language->delete();

        return redirect()
            ->route('admin.languages.index')
            ->with('success', __('admin/languages.deleted'));
    }

    /**
     * Persist the uploaded icon using the language id as the filename.
     */
    private function storeIcon(UploadedFile $icon, int $languageId): string
    {
        $directory = public_path('uploads/icon');

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $this->deleteIconFiles($languageId);

        $extension = strtolower($icon->getClientOriginalExtension());
        $filename = $languageId . '.' . $extension;

        $icon->move($directory, $filename);

        return 'uploads/icon/' . $filename;
    }

    /**
     * Delete all icon files for the given language id (any extension).
     */
    private function deleteIconFiles(int $languageId): void
    {
        $directory = public_path('uploads/icon');

        if (!File::exists($directory)) {
            return;
        }

        $pattern = $directory . DIRECTORY_SEPARATOR . $languageId . '.*';
        foreach (glob($pattern) as $file) {
            File::delete($file);
        }
    }
}
