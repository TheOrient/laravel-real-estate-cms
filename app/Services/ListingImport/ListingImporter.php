<?php

namespace App\Services\ListingImport;

use App\Helpers\CategoryHelper;
use App\Models\Listing;
use App\Services\ListingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ListingImporter
{
    public function __construct(private ImportValidator $validator) {}

    public function import(string $manifest, mixed $agentId, bool $publish = false, bool $dryRun = false): array
    {
        if (! Schema::hasColumn('listings', 'import_reference')) {
            throw ValidationException::withMessages(['database' => __('listing_import.migration_required')]);
        }
        $bundle = $this->validator->prepare($manifest, $agentId, $publish);
        $directory = 'uploads/listings/imports/'.Str::uuid();

        try {
            $result = DB::transaction(function () use ($bundle, $publish, $dryRun, $directory) {
                $existing = Listing::withTrashed()->where('import_reference', $bundle['reference'])->lockForUpdate()->first();
                if ($existing && ($existing->trashed() || ! hash_equals($existing->import_fingerprint ?? '', $bundle['fingerprint']))) {
                    throw ValidationException::withMessages(['reference' => __('listing_import.reference_conflict')]);
                }
                if ($dryRun) {
                    return ['action' => 'validated', 'listing' => $existing];
                }
                if ($existing) {
                    // Retries never overwrite content, replace photos, or unpublish a listing.
                    // A draft-to-public transition must be requested explicitly with --publish.
                    if ($publish && (! $existing->is_active || ! $existing->is_approved)) {
                        $existing->update($this->publicationValues(true));
                        $this->updateCounts($existing);

                        return ['action' => 'published', 'listing' => $existing];
                    }

                    return ['action' => 'unchanged', 'listing' => $existing];
                }

                $listing = new Listing($bundle['values'] + $this->publicationValues($publish));
                $listing->forceFill(['import_reference' => $bundle['reference'], 'import_fingerprint' => $bundle['fingerprint']])->save();
                $listing->descriptions()->create($bundle['description'] + [
                    'slug' => Str::slug($bundle['description']['title']).'-'.$listing->id,
                ]);
                $listing->attributeValues()->sync($bundle['attributes']['options']);
                foreach ($bundle['attributes']['custom'] as $id => $value) {
                    $listing->customAttributeValues()->create(['attribute_id' => $id, 'value' => $value]);
                }
                foreach ($bundle['photos'] as $index => $photo) {
                    $path = $directory.'/'.sprintf('%02d.webp', $index + 1);
                    if (! Storage::disk('uploads')->put($path, $photo['encoded'])) {
                        throw new RuntimeException(__('listing_import.storage_failed'));
                    }
                    $relative = Str::after($path, 'uploads/listings/');
                    $listing->images()->create([
                        'image' => $relative, 'sort_order' => $index, 'is_primary' => $index === 0, 'is_active' => true,
                    ]);
                    if ($index === 0) {
                        $listing->update(['image' => $relative]);
                    }
                }
                $this->updateCounts($listing);

                return ['action' => $publish ? 'created_published' : 'created_draft', 'listing' => $listing->fresh()];
            });
        } catch (Throwable $exception) {
            // Only this attempt's UUID directory is ever removed. Source photos are untouched.
            try {
                Storage::disk('uploads')->deleteDirectory($directory);
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }
            throw $exception;
        }

        if (! $dryRun && $result['action'] !== 'unchanged') {
            // Cache trouble must not report a successfully committed import as a failure.
            try {
                app(ListingService::class)->clearSimilarListingsCacheForCategory($bundle['values']['category_id']);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $result + ['reference' => $bundle['reference'], 'photo_count' => count($bundle['photos'])];
    }

    private function publicationValues(bool $publish): array
    {
        return ['status' => 'active', 'is_active' => $publish, 'is_approved' => $publish,
            'is_sent_to_approver' => false, 'approved_at' => $publish ? now() : null];
    }

    private function updateCounts(Listing $listing): void
    {
        for ($category = $listing->category; $category; $category = $category->parent) {
            CategoryHelper::updateCategoryCount($category->id);
        }
    }
}
