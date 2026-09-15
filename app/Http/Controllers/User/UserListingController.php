<?php

namespace App\Http\Controllers\User;

use App\Constants\ListingAttributeDisplayTypeConstant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\Listing;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\City;
use App\Models\District;
use App\Models\Neighborhood;
use App\Models\ListingImage;
use App\Models\ListingAttributeValue;
use App\Models\ListingAttributeCustomValue;
use App\Models\Language;
use App\Services\ListingService;
use App\Services\ImageService;
use Illuminate\Support\Facades\Validator;
class UserListingController extends Controller
{
    protected ListingService $listingService;
    protected ImageService $imageService;

    public function __construct(ListingService $listingService, ImageService $imageService)
    {
        $this->middleware('auth');
        $this->listingService = $listingService;
        $this->imageService = $imageService;
    }

    /**
     * Step 1: Category Selection
     */
    public function create()
    {
        $user = Auth::user();

        // Check if user has phone number
        if (empty($user->phone)) {
            return redirect()
                ->route('user.profile')
                ->with('error', __('listings.phone_required'));
        }

        // Clear any previous listing creation session data
        session()->forget(['listing_category_id', 'listing_essentials']);

        // Load categories with all nested children for unlimited hierarchy
        $categories = Category::with('childrenRecursive','description')
            ->whereNull('parent_id')
            ->get();

        return view('user.listings.create.step1-category', compact('categories'));
    }

    /**
     * Store step 1 - category selection
     */
    public function storeStep1(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id'
        ]);

        $user = Auth::user();

        // Check if user has phone number
        if (empty($user->phone)) {
            return redirect()
                ->route('user.profile')
                ->with('error', __('listings.phone_required'));
        }

        // Store category in session and redirect to step 2
        session(['listing_category_id' => $request->category_id]);
        return redirect()->route('user.listings.create.step2');
    }

    /**
     * Step 2: Essential Information (title, description, price, location)
     */
    public function createStep2()
    {
        $user = Auth::user();

        // Check if user has phone number
        if (empty($user->phone)) {
            return redirect()
                ->route('user.profile')
                ->with('error', __('listings.phone_required'));
        }

        if (!session('listing_category_id')) {
            return redirect()->route('user.listings.create')->with('error', __('listings.select_category_first'));
        }

        $category = Category::find(session('listing_category_id'));
        $cities = City::orderBy('name')->get();

        $defaultLanguage = Language::where('is_active', true)->where('is_default', true)->first()
            ?? Language::where('is_active', true)->orderBy('sort_order')->first();

        return view('user.listings.create.step2-essentials', compact('category', 'cities', 'defaultLanguage'));
    }

    /**
     * Store step 2 - essential information
     */
    public function storeStep2(Request $request)
    {
        // Get validation rules from ListingDescriptionService
        $descriptionService = app(\App\Services\ListingDescriptionService::class);
        $descriptionRules = $descriptionService->getValidationRules();
        $descriptionMessages = $descriptionService->getValidationMessages();

        $rules = array_merge([
            'price' => 'required|numeric|min:0',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id',
            'neighborhood_id' => 'nullable|exists:neighborhoods,id',
            'user_id' => 'prohibited', // Prevent user_id manipulation

            // Optional map pick — accepted at step 2 so the user can
            // pin the location alongside the city/district selectors.
            'coordinates'           => 'nullable|array',
            'coordinates.latitude'  => 'nullable|numeric|between:-90,90',
            'coordinates.longitude' => 'nullable|numeric|between:-180,180',
        ], $descriptionRules);

        $request->validate($rules, $descriptionMessages);

        // Store essential data in session (including descriptions)
        $essentials = $request->only([
            'price', 'city_id', 'district_id', 'neighborhood_id',
        ]);

        // Store descriptions separately
        $essentials['descriptions'] = $request->input('descriptions', []);

        // Coordinates: keep both as a sub-array so step3 can pull them
        // when actually creating the listing.
        $coords = $request->input('coordinates', []);
        if (!empty($coords['latitude']) && !empty($coords['longitude'])) {
            $essentials['coordinates'] = [
                'latitude'  => $coords['latitude'],
                'longitude' => $coords['longitude'],
            ];
        }

        session(['listing_essentials' => $essentials]);

        return redirect()->route('user.listings.create.step3');
    }

    /**
     * Step 3: Attributes and Features
     */
    public function createStep3()
    {
        $user = Auth::user();

        // Check if user has phone number
        if (empty($user->phone)) {
            return redirect()
                ->route('user.profile')
                ->with('error', __('listings.phone_required'));
        }

        if (!session('listing_category_id') || !session('listing_essentials')) {
            return redirect()->route('user.listings.create')->with('error', __('listings.complete_previous_steps'));
        }

        $category = Category::find(session('listing_category_id'));

        // Get all attributes including parent categories' attributes with their values
        $attributes = $category->getAllAttributes(['values']);


        return view('user.listings.create.step3-attributes', compact('category', 'attributes'));
    }

    /**
     * Store step 3 - attributes and finalize listing
     */
    public function storeStep3(Request $request)
    {
        $user = Auth::user();

        // Check if user has phone number
        if (empty($user->phone)) {
            return redirect()
                ->route('user.profile')
                ->with('error', __('listings.phone_required'));
        }

        if (!session('listing_category_id') || !session('listing_essentials')) {
            return redirect()->route('user.listings.create')->with('error', __('listings.complete_previous_steps'));
        }

        // Get category and its attributes for validation
        $category = Category::findOrFail(session('listing_category_id'));
        $attributes = $category->attributes;

        // Validation rules for attributes
        $rules = [];
        foreach ($attributes as $attribute) {
            $attributeKey = null;

            // Determine correct field name based on attribute type
            if (!in_array(
                $attribute->display_type_user_panel, [ListingAttributeDisplayTypeConstant::NUMBER, ListingAttributeDisplayTypeConstant::INPUT, ListingAttributeDisplayTypeConstant::TEXTAREA,]) && $attribute->values->count() > 0) {
                // For select/radio/checkbox with predefined values
                $attributeKey = 'attributes.' . $attribute->id;
            } else {
                // For text/number/textarea custom inputs
                $attributeKey = 'custom_attributes.' . $attribute->id;
            }

            // Add required validation if needed
            if ($attribute->is_required) {
                $rules[$attributeKey] = 'required';
            }

            // Add specific validation for number type
            if ($attribute->display_type_user_panel === ListingAttributeDisplayTypeConstant::NUMBER) {
                $numberRules = ['nullable', 'numeric'];
                if ($attribute->min_value !== null) {
                    $numberRules[] = 'min:' . $attribute->min_value;
                }
                if ($attribute->max_value !== null) {
                    $numberRules[] = 'max:' . $attribute->max_value;
                }
                $rules['custom_attributes.' . $attribute->id] = $attribute->is_required ?
                    array_merge(['required'], array_slice($numberRules, 1)) : $numberRules;
            }
        }

        // Custom validation messages
        $messages = [
            'attributes.*.required' => ':attribute alanı zorunludur.',
            'custom_attributes.*.required' => ':attribute alanı zorunludur.',
            'custom_attributes.*.numeric' => ':attribute alanı sayısal olmalıdır.',
            'custom_attributes.*.min' => ':attribute alanı en az :min olmalıdır.',
            'custom_attributes.*.max' => ':attribute alanı en fazla :max olmalıdır.',
        ];

        // Custom attribute names for better error messages
        $attributeNames = [];
        foreach ($attributes as $attribute) {
            $attributeNames['attributes.' . $attribute->id] = $attribute->name;
            $attributeNames['custom_attributes.' . $attribute->id] = $attribute->name;
        }

        if (!empty($rules)) {
            $request->validate($rules, $messages, $attributeNames);
        }

        try {
            DB::beginTransaction();

            // Get data from session
            $categoryId = session('listing_category_id');
            $essentials = session('listing_essentials');

            // Coordinates carried over from step 2's optional map pick.
            $coords = $essentials['coordinates'] ?? [];

            $listingData = [
                'price' => $essentials['price'],
                'category_id' => $categoryId,
                'user_id' => Auth::id(), // User ID is automatically set to prevent tampering
                'city_id' => $essentials['city_id'],
                'district_id' => $essentials['district_id'],
                'neighborhood_id' => $essentials['neighborhood_id'] ?? null,
                'latitude'  => $coords['latitude']  ?? null,
                'longitude' => $coords['longitude'] ?? null,
                'status' => 'active',
                'is_approved' => true,
                'is_active' => true,
                'is_sent_to_approver' => false,
                'sent_to_approver_at' => null,
                'approved_at' => now(),
                'allow_whatsapp' => false,
                'expires_at' => now()->addDays(30),
            ];

            // Create listing
            $listing = Listing::create($listingData);

            // Sync listing descriptions (multi-language support)
            if (isset($essentials['descriptions']) && !empty($essentials['descriptions'])) {
                $descriptionService = app(\App\Services\ListingDescriptionService::class);
                $descriptionService->syncDescriptions($listing, $essentials['descriptions']);
            }

            // Handle attributes
            if ($request->has('attributes')) {
                $attributes = $request['attributes'];
                foreach ($attributes as $attributeId => $value) {
                    $attribute = Attribute::find($attributeId);
                    if (!$attribute) continue; // Skip if attribute not found

                    if (is_array($value) && $attribute->allow_multiple && in_array($attribute->display_type_user_panel, ['checkbox'])) {
                        // Multiple choice attribute (checkbox)
                        foreach ($value as $attrValueId) {
                            if ($attrValueId) { // Skip empty values
                                ListingAttributeValue::create([
                                    'listing_id' => $listing->id,
                                    'attribute_value_id' => $attrValueId
                                ]);
                            }
                        }
                    } elseif (!is_array($value) && $value) {
                        // Single choice attribute (select, radio)
                        $attributeValue = AttributeValue::find($value);
                        if ($attributeValue) {
                            ListingAttributeValue::create([
                                'listing_id' => $listing->id,
                                'attribute_value_id' => $value
                            ]);
                        } else if (in_array($attribute->display_type_user_panel, ListingAttributeDisplayTypeConstant::getFreeTextInputTypes())) {
                            // Custom value for free text inputs
                            ListingAttributeCustomValue::create([
                                'listing_id' => $listing->id,
                                'attribute_id' => $attributeId,
                                'value' => $value
                            ]);
                        }
                    }
                }
            }

            // Handle custom attribute values
            if ($request->has('custom_attributes')) {
                foreach ($request->custom_attributes as $attributeId => $value) {
                    if ($value) {
                        ListingAttributeCustomValue::create([
                            'listing_id' => $listing->id,
                            'attribute_id' => $attributeId,
                            'value' => $value
                        ]);
                    }
                }
            }

            DB::commit();

            // Don't clear session data yet, we need it for step 4
            // session()->forget(['listing_category_id', 'listing_essentials']);

            return redirect()->route('user.listings.create.step4', $listing)->with('success', __('listings.created_add_photos'));

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Listing creation error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', __('listings.creation_error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Step 4: Images (part of creation flow)
     */
    public function createStep4(Listing $listing)
    {
        // Check if user owns this listing
        if ($listing->user_id !== Auth::id()) {
            abort(403);
        }

        return view('user.listings.create.step4-images', compact('listing'));
    }

    /**
     * Store step 4 - images and finalize
     */
    public function storeStep4(Request $request, Listing $listing)
    {
        // Check if user owns this listing
        if ($listing->user_id !== Auth::id()) {
            abort(403);
        }

        // Validate optional video fields. URL is checked against the
        // VideoEmbedService allow-list so unsupported hosts (TikTok,
        // Twitch, etc.) are rejected before we save the value.
        $request->validate([
            'video_url' => ['nullable', 'url', 'max:255', function ($attr, $value, $fail) {
                if ($value && ! \App\Services\VideoEmbedService::isValidExternalUrl($value)) {
                    $fail(__('listings.video_invalid_url'));
                }
            }],
            'video_file' => [
                'nullable',
                'file',
                'mimetypes:' . implode(',', \App\Services\VideoEmbedService::allowedFileMimes()),
                'max:' . (int) ceil(\App\Services\VideoEmbedService::maxFileBytes() / 1024),
            ],
        ]);

        try {
            DB::beginTransaction();

            // Persist video info first (independent of image handling
            // so an upload failure in images doesn't lose the video URL).
            $videoUpdates = [];
            if ($request->filled('video_url')) {
                $videoUpdates['video_url'] = $request->input('video_url');
            } else if ($request->has('video_url')) {
                $videoUpdates['video_url'] = null; // explicitly cleared
            }

            if ($request->hasFile('video_file')) {
                $vf = $request->file('video_file');
                $subdir = 'uploads/listings/videos/' . now()->format('Y/m');
                $abs = public_path($subdir);
                if (! is_dir($abs)) { @mkdir($abs, 0755, true); }
                $name = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $vf->getClientOriginalName());
                $vf->move($abs, $name);
                // Drop the previous file when replacing.
                if ($listing->video_file && is_file(public_path($listing->video_file))) {
                    @unlink(public_path($listing->video_file));
                }
                $videoUpdates['video_file'] = $subdir . '/' . $name;
            }
            if (!empty($videoUpdates)) {
                $listing->update($videoUpdates);
            }

            // Handle image updates (order and deletions)
            $imageIds = $request->filled('image_ids')
                ? array_filter(explode(',', $request->image_ids))
                : [];

            // Validate: Maximum 10 active images
            if (count($imageIds) > 10) {
                DB::rollBack();
                return back()->with('error', __('listings.max_photos'));
            }

            $currentImageIds = $listing->images()->pluck('id')->toArray();

            // Find deleted images (mevcut tüm resimlerden gönderilen listede olmayanlar)
            $deletedImageIds = array_diff($currentImageIds, $imageIds);

            // Delete removed images (hiç foto gönderilmediyse tüm resimleri siler)
            if (!empty($deletedImageIds)) {
                foreach ($deletedImageIds as $deletedImageId) {
                    $image = ListingImage::find($deletedImageId);
                    if ($image && $image->listing_id === $listing->id) {
                        // Delete physical file and cache
                        $imagePath = 'uploads/listings/' . $image->image;
                        $this->imageService->deleteImage($imagePath);

                        // Delete database record
                        $image->delete();
                    }
                }
            }

            // Update sort order and activate images
            if (!empty($imageIds)) {
                foreach ($imageIds as $index => $imageId) {
                    ListingImage::where('id', $imageId)
                        ->where('listing_id', $listing->id)
                        ->update([
                            'sort_order' => $index + 1,
                            'is_primary' => $index === 0 ? 1 : 0,
                            'is_active' => 1, // Form submit edildi, artık aktif
                        ]);
                }

                // Set first image as listing's main image
                $firstImage = ListingImage::find($imageIds[0]);
                if ($firstImage) {
                    $listing->image = $firstImage->image;
                    $listing->save();
                }
            } else {
                // No images left, clear main image
                $listing->image = null;
                $listing->save();
            }

            DB::commit();

            // Clear session data
            session()->forget(['listing_category_id', 'listing_essentials']);

            $imageCount = $listing->images()->where('is_active', 1)->count();
            $message = $imageCount > 0
                ? __('listings.created_with_photos', ['count' => $imageCount])
                : __('listings.created_published_no_photos');

            return redirect()->route('user.listings.my')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('storeStep4 error: ' . $e->getMessage(), [
                'listing_id' => $listing->id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', __('listings.save_error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Image upload page (standalone - for editing existing listings)
     */
    public function images(Listing $listing)
    {
        // Check if user owns this listing
        if ($listing->user_id !== Auth::id()) {
            abort(403);
        }

        return view('user.listings.images', compact('listing'));
    }

    /**
     * Upload images
     */
    public function uploadImages(Request $request, Listing $listing)
    {
        // Check if user owns this listing
        if ($listing->user_id !== Auth::id()) {
            abort(403);
        }

        // Note: FileUploadService handles validation automatically
        $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'required|file'
        ]);

        try {
            if ($request->hasFile('images')) {
                // Use new FileUploadService for multiple file upload
                $result = uploadMultipleFiles(
                    $request->file('images'),
                    'image',
                    'listings',
                    ['custom_name' => 'listing-' . $listing->id]
                );

                if ($result['success']) {
                    // Save successful uploads to database
                    foreach ($result['uploaded'] as $key => $uploadedFile) {
                        // Remove 'uploads/listings/' prefix for database storage
                        // Helper function will add it back when displaying
                        $imagePath = str_replace('uploads/listings/', '', $uploadedFile['path']);

                        ListingImage::create([
                            'listing_id' => $listing->id,
                            'image' => $imagePath,
                            'is_primary' => $key === 0 ? 1 : 0,
                            'sort_order' => ListingImage::where('listing_id', $listing->id)->count() + 1
                        ]);
                        if($key === 0){
                            $listing->image = $imagePath;
                            $listing->save();
                        }
                    }

                    $successMessage = __('listings.photos_uploaded_published');

                    // Add info about any failed uploads
                    if ($result['total_errors'] > 0) {
                        $successMessage .= __('listings.photos_failed', ['count' => $result['total_errors']]);
                    }

                    return redirect()->route('user.listings.my')->with('success', $successMessage);
                } else {
                    return back()->with('error', __('listings.no_photos_uploaded'));
                }
            }

            return back()->with('error', __('listings.select_at_least_one_photo'));

        } catch (\Exception $e) {
            return back()->with('error', __('listings.photo_upload_error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Upload images via AJAX
     */
    public function uploadImagesAjax(Request $request, Listing $listing)
    {
        // Check if user owns this listing
        if ($listing->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz erişim.'], 403);
        }

        // Validate request
        $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'required|file|mimes:jpeg,jpg,png,gif,webp|max:2048'
        ]);

        try {
            if ($request->hasFile('images')) {
                // Check current ACTIVE image count (inactive ones will be cleaned up)
                $currentImageCount = $listing->images()->where('is_active', 1)->count();
                $newImageCount = count($request->file('images'));
                if (($currentImageCount + $newImageCount) > 10) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Maksimum 10 fotoğraf yükleyebilirsiniz.'
                    ], 422);
                }

                // Upload files using FileUploadService
                $result = uploadMultipleFiles(
                    $request->file('images'),
                    'image',
                    'listings',
                    ['custom_name' => 'listing-' . $listing->id]
                );

                if ($result['success']) {
                    $uploadedImages = [];
                    $isFirstImage = $listing->images()->count() === 0;

                    // Save successful uploads to database
                    foreach ($result['uploaded'] as $key => $uploadedFile) {
                        // Remove 'uploads/listings/' prefix for database storage
                        $imagePath = str_replace('uploads/listings/', '', $uploadedFile['path']);

                        $listingImage = ListingImage::create([
                            'listing_id' => $listing->id,
                            'image' => $imagePath,
                            'is_primary' => ($key === 0 && $isFirstImage) ? 1 : 0,
                            'sort_order' => ListingImage::where('listing_id', $listing->id)->count() + 1,
                            'is_active' => 0, // AJAX ile yüklendi, henüz form submit edilmedi
                        ]);

                        // Set first image as listing's main image if no main image exists
                        if($key === 0 && $isFirstImage){
                            $listing->image = $imagePath;
                            $listing->save();
                        }

                        $uploadedImages[] = [
                            'id' => $listingImage->id,
                            'url' => listing_image_url($imagePath, 'medium', 'crop'),
                            'is_primary' => $listingImage->is_primary,
                            'original_name' => $uploadedFile['original_name']
                        ];
                    }

                    return response()->json([
                        'success' => true,
                        'message' => count($uploadedImages) . ' fotoğraf başarıyla yüklendi.',
                        'images' => $uploadedImages,
                        'total_images' => $listing->images()->count() + count($uploadedImages),
                        'errors' => $result['total_errors'] > 0 ? $result['errors'] : null
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Fotoğraf yükleme başarısız.',
                        'errors' => $result['errors']
                    ], 422);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Lütfen en az bir fotoğraf seçin.'
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fotoğraf yükleme sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Edit listing
     */
    public function edit(Listing $listing)
    {
        // Check if user owns this listing
        if ($listing->user_id !== Auth::id()) {
            abort(403);
        }

        // Clear any previous listing creation session data
        session()->forget(['listing_category_id', 'listing_essentials']);

        // Load the listing with all necessary relationships
        $listing->load([
            'attributeValues.attribute',
            'customAttributeValues.attribute',
        ]);

        $categories = Category::where('parent_id', null)->get();
        $cities = City::orderBy('name')->get();
        $districts = District::where('city_id', $listing->city_id)->orderBy('name')->get();
        $neighborhoods = Neighborhood::where('district_id', $listing->district_id)->orderBy('name')->get();
        $attributes = $listing->category->getAllAttributes(['values']);

        $defaultLanguage = Language::where('is_active', true)->where('is_default', true)->first()
            ?? Language::where('is_active', true)->orderBy('sort_order')->first();
        $descriptionService = app(\App\Services\ListingDescriptionService::class);
        $descriptions = $descriptionService->prepareDescriptionsForEdit($listing);

        return view('user.listings.edit', compact('listing', 'categories', 'cities', 'districts', 'neighborhoods', 'attributes', 'defaultLanguage', 'descriptions'));
    }

    /**
     * Update listing
     */
    public function update(Request $request, Listing $listing)
    {
        // Check if user owns this listing
        if ($listing->user_id !== Auth::id()) {
            abort(403);
        }

        // Get validation rules from ListingDescriptionService
        $descriptionService = app(\App\Services\ListingDescriptionService::class);
        $descriptionRules = $descriptionService->getValidationRules();
        $descriptionMessages = $descriptionService->getValidationMessages();

        // Base validation rules
        $rules = array_merge([
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id',
            'neighborhood_id' => 'nullable|exists:neighborhoods,id',
            'user_id' => 'prohibited', // Prevent user_id manipulation
            'image_ids' => 'nullable|string', // Resim ID'leri ve sıralaması

            // Video: optional URL (YouTube/Vimeo) and/or file upload.
            // Custom rule on the URL so non-supported hosts are rejected
            // before we ever try to render an iframe.
            'video_url' => ['nullable', 'url', 'max:255', function ($attr, $value, $fail) {
                if ($value && ! \App\Services\VideoEmbedService::isValidExternalUrl($value)) {
                    $fail(__('listings.video_invalid_url'));
                }
            }],
            'video_file' => [
                'nullable',
                'file',
                'mimetypes:' . implode(',', \App\Services\VideoEmbedService::allowedFileMimes()),
                'max:' . (int) ceil(\App\Services\VideoEmbedService::maxFileBytes() / 1024),
            ],
            'remove_video_file' => 'nullable|boolean',

            // Optional map coordinates from the <x-map-picker /> component.
            'coordinates'            => 'nullable|array',
            'coordinates.latitude'   => 'nullable|numeric|between:-90,90',
            'coordinates.longitude'  => 'nullable|numeric|between:-180,180',
        ], $descriptionRules);

        // Get category and its attributes for validation
        $category = Category::findOrFail($request->category_id);
        $attributes = $category->getAllAttributes(['values']);

        // Add validation rules for attributes
        foreach ($attributes as $attribute) {
            $attributeKey = null;

            // Determine correct field name based on attribute type
            if (in_array($attribute->display_type_user_panel, ListingAttributeDisplayTypeConstant::getPredefinedValueTypes()) && $attribute->values->count() > 0) {
                // For select/radio/checkbox with predefined values
                $attributeKey = 'attributes.' . $attribute->id;
            } else {
                // For text/number/textarea custom inputs
                $attributeKey = 'custom_attributes.' . $attribute->id;
            }

            // Add required validation if needed
            if ($attribute->is_required) {
                $rules[$attributeKey] = 'required';
            }

            // Add specific validation for number type
            if ($attribute->display_type_user_panel === 'number') {
                $numberRules = ['nullable', 'numeric'];
                if ($attribute->min_value !== null) {
                    $numberRules[] = 'min:' . $attribute->min_value;
                }
                if ($attribute->max_value !== null) {
                    $numberRules[] = 'max:' . $attribute->max_value;
                }
                $rules['custom_attributes.' . $attribute->id] = $attribute->is_required ?
                    array_merge(['required'], array_slice($numberRules, 1)) : $numberRules;
            }
        }

        // Custom validation messages
        $messages = array_merge([
            'attributes.*.required' => ':attribute alanı zorunludur.',
            'custom_attributes.*.required' => ':attribute alanı zorunludur.',
            'custom_attributes.*.numeric' => ':attribute alanı sayısal olmalıdır.',
            'custom_attributes.*.min' => ':attribute alanı en az :min olmalıdır.',
            'custom_attributes.*.max' => ':attribute alanı en fazla :max olmalıdır.',
        ], $descriptionMessages);

        // Custom attribute names for better error messages
        $attributeNames = [];
        foreach ($attributes as $attribute) {
            $attributeNames['attributes.' . $attribute->id] = $attribute->name;
            $attributeNames['custom_attributes.' . $attribute->id] = $attribute->name;
        }

        $request->validate($rules, $messages, $attributeNames);

        try {
            DB::beginTransaction();

            // Update listing (koordinatlar ücretsiz sürümde düzenlenmez; mevcut DB değeri korunur)
            $updateData = [
                'price' => $request->price,
                'category_id' => $request->category_id,
                'city_id' => $request->city_id,
                'district_id' => $request->district_id,
                'neighborhood_id' => $request->neighborhood_id,
                'allow_whatsapp' => false,
                'is_approved' => true,
                'is_sent_to_approver' => false,
                'sent_to_approver_at' => null,
                'approved_at' => $listing->approved_at ?? now(),
                'expires_at' => now()->addDays(30),
                // Video URL: empty string → null so the show view's
                // "do we have a video?" check is unambiguous.
                'video_url' => $request->filled('video_url') ? $request->input('video_url') : null,
            ];

            // Persist coordinates when the picker provided them. Empty
            // values are ignored so we don't clobber existing data when
            // the picker isn't touched.
            $coords = $request->input('coordinates', []);
            if (!empty($coords['latitude']) && !empty($coords['longitude'])) {
                $updateData['latitude']  = $coords['latitude'];
                $updateData['longitude'] = $coords['longitude'];
            }

            // Handle the optional video file: new upload, removal, or leave
            // the existing one in place.
            if ($request->boolean('remove_video_file') && $listing->video_file) {
                $oldFull = public_path($listing->video_file);
                if (is_file($oldFull)) {
                    @unlink($oldFull);
                }
                $updateData['video_file'] = null;
            }

            if ($request->hasFile('video_file')) {
                $vf = $request->file('video_file');
                $subdir = 'uploads/listings/videos/' . now()->format('Y/m');
                $full = public_path($subdir);
                if (! is_dir($full)) {
                    @mkdir($full, 0755, true);
                }
                $name = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $vf->getClientOriginalName());
                $vf->move($full, $name);

                // Replace old file if present.
                if ($listing->video_file && is_file(public_path($listing->video_file))) {
                    @unlink(public_path($listing->video_file));
                }
                $updateData['video_file'] = $subdir . '/' . $name;
            }

            $listing->update($updateData);

            // Sync listing descriptions (multi-language support)
            if ($request->has('descriptions')) {
                $descriptionService->syncDescriptions($listing, $request->input('descriptions'));
            }

            // Clear existing attributes
            ListingAttributeValue::where('listing_id', $listing->id)->delete();
            ListingAttributeCustomValue::where('listing_id', $listing->id)->delete();

            // Handle attributes using ListingService for consistency
            $category = Category::findOrFail($request->category_id);
            $categoryAttributes = $category->getAllAttributes();

            foreach ($categoryAttributes as $attribute) {
                // Handle predefined value attributes (select, radio, checkbox)
                if (in_array($attribute->display_type_user_panel, ['select', 'radio', 'checkbox']) && $attribute->values->count() > 0) {
                    $attributeValues = $request->input('attributes.' . $attribute->id);
                    if ($attributeValues) {
                        if (is_array($attributeValues)) {
                            // Checkbox with multiple values
                            foreach ($attributeValues as $valueId) {
                                if ($valueId) {
                                    ListingAttributeValue::create([
                                        'listing_id' => $listing->id,
                                        'attribute_value_id' => $valueId
                                    ]);
                                }
                            }
                        } else {
                            // Single value (select, radio)
                            ListingAttributeValue::create([
                                'listing_id' => $listing->id,
                                'attribute_value_id' => $attributeValues
                            ]);
                        }
                    }
                } else {
                    // Handle custom value attributes (text, number, textarea)
                    $customValue = $request->input('custom_attributes.' . $attribute->id);
                    if ($customValue !== null && $customValue !== '') {
                        ListingAttributeCustomValue::create([
                            'listing_id' => $listing->id,
                            'attribute_id' => $attribute->id,
                            'value' => (string) $customValue
                        ]);
                    }
                }
            }

            // Handle image updates (order and deletions)
            $imageIds = $request->filled('image_ids')
                ? array_filter(explode(',', $request->image_ids))
                : [];

            // Validate: Maximum 10 active images
            if (count($imageIds) > 10) {
                return back()->with('error', __('listings.max_photos'))->withInput();
            }

            $currentImageIds = $listing->images()->pluck('id')->toArray();

            // Find deleted images (mevcut tüm resimlerden gönderilen listede olmayanlar)
            $deletedImageIds = array_diff($currentImageIds, $imageIds);

            // Delete removed images (hiç foto gönderilmediyse tüm resimleri siler)
            if (!empty($deletedImageIds)) {
                foreach ($deletedImageIds as $deletedImageId) {
                    $image = ListingImage::find($deletedImageId);
                    if ($image) {
                        // Delete physical file and cache
                        $imagePath = 'uploads/listings/' . $image->image;
                        $this->imageService->deleteImage($imagePath);

                        // Delete database record
                        $image->delete();
                    }
                }
            }

            // Update sort order and activate images
            if (!empty($imageIds)) {
                foreach ($imageIds as $index => $imageId) {
                    ListingImage::where('id', $imageId)
                        ->where('listing_id', $listing->id)
                        ->update([
                            'sort_order' => $index + 1,
                            'is_primary' => $index === 0 ? 1 : 0,
                            'is_active' => 1, // Form submit edildi, artık aktif
                        ]);
                }

                // Set first image as listing's main image
                $firstImage = ListingImage::find($imageIds[0]);
                if ($firstImage) {
                    $listing->image = $firstImage->image;
                    $listing->save();
                }
            } else {
                // No images left, clear main image
                $listing->image = null;
                $listing->save();
            }

            DB::commit();

            // Clear similar listings cache for both old and new categories (in case category changed)
            $this->listingService->clearSimilarListingsCacheForCategory($listing->getOriginal('category_id'));
            if ($listing->getOriginal('category_id') != $listing->category_id) {
                $this->listingService->clearSimilarListingsCacheForCategory($listing->category_id);
            }

            return redirect()->route('user.listings.my')->with('success', __('listings.updated_successfully'));

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Listing update error: ' . $e->getMessage(), [
                'listing_id' => $listing->id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', __('listings.update_error'));
        }
    }

    /**
     * Delete listing
     */
    public function destroy(Listing $listing)
    {
        $this->authorizeOwner($listing);

        // Use the listing service for consistent image cleanup
        $this->listingService->deleteListing($listing->id);

        return redirect()->route('user.listings.my')->with('success', __('listings.deleted_successfully'));
    }

    /* ------------------------------------------------------------------ */
    /* Danışman paneli — ilan durumu yönetimi                              */
    /* ------------------------------------------------------------------ */

    /**
     * Yayına al: ilan hem aktif hem onaylı hâle gelir ve sitede görünür.
     * Tek ofisli kurulumda onay makamı danışmanın kendisidir.
     */
    public function publish(Listing $listing)
    {
        $this->authorizeOwner($listing);

        if (! $listing->images()->exists() && ! $listing->image) {
            return back()->with('error', __('user.publish_needs_photo'));
        }

        $listing->forceFill([
            'is_active'   => true,
            'is_approved' => true,
            'approved_at' => $listing->approved_at ?? now(),
        ])->save();

        return back()->with('success', __('user.listing_published'));
    }

    /** Yayından kaldır: ilan taslağa döner, site ve sitemap'ten çıkar. */
    public function unpublish(Listing $listing)
    {
        $this->authorizeOwner($listing);

        $listing->forceFill(['is_approved' => false])->save();

        return back()->with('success', __('user.listing_unpublished'));
    }

    /** Aktif/pasif arasında geçiş. Pasif ilan sitede listelenmez. */
    public function toggleActive(Listing $listing)
    {
        $this->authorizeOwner($listing);

        $listing->forceFill(['is_active' => ! $listing->is_active])->save();

        return back()->with('success', $listing->is_active
            ? __('user.listing_activated')
            : __('user.listing_deactivated'));
    }

    /** Öne çıkarılan ilanlar ana sayfada üstte gösterilir. */
    public function toggleFeatured(Listing $listing)
    {
        $this->authorizeOwner($listing);

        $listing->forceFill(['is_featured' => ! $listing->is_featured])->save();

        return back()->with('success', $listing->is_featured
            ? __('user.listing_featured_on')
            : __('user.listing_featured_off'));
    }

    /** İlan bu danışmana ait değilse işlem yapılamaz. */
    private function authorizeOwner(Listing $listing): void
    {
        $user = Auth::user();

        if ($listing->user_id !== $user?->id && ! ($user && $user->isAdmin())) {
            abort(403);
        }
    }
}
