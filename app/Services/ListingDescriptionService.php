<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingDescription;
use App\Models\Language;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListingDescriptionService
{
    /**
     * Sync listing descriptions for all languages
     *
     * @param Listing $listing
     * @param array $descriptionsData Array indexed by language_id
     * @return void
     */
    public function syncDescriptions(Listing $listing, array $descriptionsData): void
    {
        DB::beginTransaction();

        try {
            $defaultLanguage = Language::where('is_default', true)->first();

            foreach ($descriptionsData as $languageId => $data) {
                // Skip if both title and description are empty (except for default language)
                $title = trim($data['title'] ?? '');
                $description = trim($data['description'] ?? '');

                if (empty($title) && empty($description)) {
                    // If this is not the default language, skip it
                    if ($languageId != $defaultLanguage->id) {
                        continue;
                    }
                }

                // Auto-generate slug if not provided
                $slug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($title);

                // Make slug unique by appending listing ID
                $slug = $slug . '-' . $listing->id;

                // Update or create description
                ListingDescription::updateOrCreate(
                    [
                        'listing_id' => $listing->id,
                        'language_id' => $languageId,
                    ],
                    [
                        'title' => $title,
                        'slug' => $slug,
                        'description' => $description,
                    ]
                );
            }

            DB::commit();

            Log::info('Listing descriptions synced', [
                'listing_id' => $listing->id,
                'languages_count' => count($descriptionsData)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync listing descriptions', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Prepare descriptions array for edit form
     * Returns array indexed by language_id with title, slug, description
     *
     * @param Listing $listing
     * @return array
     */
    public function prepareDescriptionsForEdit(Listing $listing): array
    {
        $descriptions = [];

        $listingDescriptions = $listing->descriptions()->get();

        foreach ($listingDescriptions as $desc) {
            $descriptions[$desc->language_id] = [
                'title' => $desc->title,
                'slug' => $desc->slug,
                'description' => $desc->description,
            ];
        }

        return $descriptions;
    }

    /**
     * Validate descriptions data
     * Ensures default language has required fields
     *
     * @param array $descriptionsData
     * @return array Validation rules
     */
    public function getValidationRules(): array
    {
        $defaultLanguage = Language::where('is_default', true)->first();

        if (!$defaultLanguage) {
            return [];
        }

        return [
            "descriptions.{$defaultLanguage->id}.title" => 'required|string|max:255',
            "descriptions.{$defaultLanguage->id}.description" => 'required|string',
            'descriptions.*.title' => 'nullable|string|max:255',
            'descriptions.*.description' => 'nullable|string',
            'descriptions.*.slug' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get validation messages
     *
     * @return array
     */
    public function getValidationMessages(): array
    {
        $defaultLanguage = Language::where('is_default', true)->first();

        return [
            "descriptions.{$defaultLanguage->id}.title.required" => 'Varsayılan dil için başlık zorunludur.',
            "descriptions.{$defaultLanguage->id}.description.required" => 'Varsayılan dil için açıklama zorunludur.',
            'descriptions.*.title.max' => 'Başlık en fazla 255 karakter olabilir.',
        ];
    }
}
