<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\City;
use App\Models\District;
use App\Models\Language;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Neighborhood;
use App\Models\User;
use App\Services\ListingImport\ListingImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class ListingImportTest extends TestCase
{
    use RefreshDatabase;

    private string $bundleDirectory;

    private User $agent;

    private Category $category;

    private Attribute $area;

    private array $manifest;

    private int $initialCount;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
        Language::clearRuntimeCache();
        $this->bundleDirectory = sys_get_temp_dir().'/listing-import-test-'.Str::uuid();
        File::makeDirectory($this->bundleDirectory);
        foreach (['living-room.jpg', 'balcony.jpg'] as $photo) {
            $upload = UploadedFile::fake()->image($photo, 120, 80);
            File::copy($upload->getPathname(), $this->bundleDirectory.'/'.$photo);
        }
        $language = Language::updateOrCreate(['code' => 'tr'], ['title' => 'Türkçe', 'is_default' => true, 'is_active' => true]);
        $this->agent = User::create(['first_name' => 'Import', 'last_name' => 'Agent',
            'email' => 'import@example.test', 'password' => 'test-password', 'user_role' => 'agent', 'is_active' => true]);
        $city = City::create(['name' => 'Test Aydın', 'slug' => 'test-aydin', 'is_active' => true]);
        $district = District::create(['city_id' => $city->id, 'name' => 'Test Kuşadası', 'slug' => 'test-kusadasi', 'is_active' => true]);
        $neighborhood = Neighborhood::create(['district_id' => $district->id, 'name' => 'Test Karaova', 'slug' => 'test-karaova', 'is_active' => true]);
        $parent = Category::create(['is_active' => true]);
        $this->category = Category::create(['is_active' => true, 'parent_id' => $parent->id]);
        $this->category->descriptions()->create(['language_id' => $language->id, 'name' => 'Satılık Daire', 'slug' => 'test/satilik-daire']);
        $rooms = Attribute::create(['display_type_user_panel' => 'select', 'is_required' => true]);
        $rooms->descriptions()->create(['language_id' => $language->id, 'name' => 'Oda sayısı']);
        $option = $rooms->values()->create(['order' => 1]);
        $option->descriptions()->create(['language_id' => $language->id, 'value' => '2+1']);
        $this->area = Attribute::create(['display_type_user_panel' => 'number', 'is_required' => true, 'min_value' => 1, 'max_value' => 10000]);
        $this->area->descriptions()->create(['language_id' => $language->id, 'name' => 'Brüt m²']);
        $parent->attributes()->attach($rooms->id);
        $this->category->attributes()->attach($this->area->id);
        $this->manifest = [
            'reference' => 'test-portfolio-001', 'title' => 'Karaova Kuşadası satılık daire',
            'description' => 'Karaova mahallesinde, iki odalı ve balkonlu daire. Ayrıntılı bilgi için ofisimizle iletişime geçin.',
            'price' => '4250000.00', 'category_id' => $this->category->id,
            'city_id' => $city->id, 'district_id' => $district->id, 'neighborhood_id' => $neighborhood->id,
            'latitude' => 37.8, 'longitude' => 27.3,
            'attributes' => [$rooms->id => [$option->id], $this->area->id => '110'],
            'photos' => ['living-room.jpg', 'balcony.jpg'],
        ];
        $this->initialCount = Listing::withTrashed()->count();
    }

    protected function tearDown(): void
    {
        ListingImage::flushEventListeners();
        Language::clearRuntimeCache();
        if (isset($this->bundleDirectory)) {
            File::deleteDirectory($this->bundleDirectory);
        }
        parent::tearDown();
    }

    private function bundle(?array $data = null): string
    {
        $path = $this->bundleDirectory.'/listing.json';
        File::put($path, json_encode($data ?? $this->manifest, JSON_THROW_ON_ERROR));

        return $path;
    }

    public function test_dry_run_validates_without_saving_rows_or_photos(): void
    {
        $this->artisan('listings:import', ['file' => $this->bundle(), '--agent' => $this->agent->id, '--dry-run' => true, '--publish' => true])
            ->expectsOutputToContain('"action": "validated"')->assertSuccessful();
        $this->assertDatabaseCount('listings', $this->initialCount);
        $this->assertSame([], Storage::disk('uploads')->allFiles());
    }

    public function test_draft_preserves_gallery_attributes_and_existing_listings(): void
    {
        $result = app(ListingImporter::class)->import($this->bundle(), $this->agent->id);
        $listing = $result['listing'];
        $this->assertSame('created_draft', $result['action']);
        $this->assertFalse($listing->is_active);
        $this->assertFalse($listing->is_approved);
        $this->assertSame('4250000.00', $listing->price);
        $this->assertSame($this->agent->id, $listing->user_id);
        $this->assertSame('karaova-kusadasi-satilik-daire-'.$listing->id, $listing->slug);
        $this->assertDatabaseCount('listings', $this->initialCount + 1);
        $this->assertCount(2, $listing->images);
        $this->assertSame([0, 1], $listing->images->pluck('sort_order')->all());
        $this->assertSame([true, false], $listing->images->pluck('is_primary')->all());
        $this->assertSame($listing->images->first()->image, $listing->image);
        $this->assertCount(1, $listing->attributeValues);
        $this->assertSame('110', $listing->customAttributeValues->first()->value);
        foreach ($listing->images as $photo) {
            $path = Storage::disk('uploads')->path('uploads/listings/'.$photo->image);
            $this->assertSame('image/webp', getimagesize($path)['mime']);
        }
        $this->assertFileExists($this->bundleDirectory.'/living-room.jpg');
        $this->assertSame(0, Listing::public()->whereKey($listing->id)->count());
    }

    public function test_same_bundle_can_publish_a_draft_without_duplicates_or_new_files(): void
    {
        $importer = app(ListingImporter::class);
        $path = $this->bundle();
        $draft = $importer->import($path, $this->agent->id);
        $files = Storage::disk('uploads')->allFiles();
        $this->assertSame('unchanged', $importer->import($path, $this->agent->id)['action']);
        $published = $importer->import($path, $this->agent->id, publish: true);
        $this->assertSame('published', $published['action']);
        $this->assertSame($draft['listing']->id, $published['listing']->id);
        $this->assertTrue($published['listing']->is_active);
        $this->assertTrue($published['listing']->is_approved);
        $this->assertNotNull($published['listing']->approved_at);
        $this->assertSame(1, Listing::public()->whereKey($published['listing']->id)->count());
        $this->assertSame(1, $this->category->fresh()->listings_count);
        $this->assertSame(1, $this->category->parent->fresh()->listings_count);
        $this->assertSame('unchanged', $importer->import($path, $this->agent->id)['action']);
        $this->assertTrue($published['listing']->fresh()->is_active);
        $this->assertDatabaseCount('listings', $this->initialCount + 1);
        $this->assertSame($files, Storage::disk('uploads')->allFiles());
    }

    public function test_command_returns_panel_and_public_links_only_when_published(): void
    {
        $this->artisan('listings:import', ['file' => $this->bundle(), '--agent' => $this->agent->id])
            ->expectsOutputToContain('"public_url": null')->assertSuccessful();
        $this->artisan('listings:import', ['file' => $this->bundle(), '--agent' => $this->agent->id, '--publish' => true])
            ->expectsOutputToContain('/listing/karaova-kusadasi-satilik-daire-')->assertSuccessful();
    }

    public function test_draft_editing_requires_the_assigned_agent_and_does_not_expose_public_content(): void
    {
        $listing = app(ListingImporter::class)->import($this->bundle(), $this->agent->id)['listing'];
        $this->get('/listing/'.$listing->slug)->assertNotFound();
        $this->get('/panel/listing/'.$listing->id.'/edit')->assertRedirect('/login');
        $this->actingAs($this->agent)->get('/panel/listing/'.$listing->id.'/edit')->assertOk()
            ->assertViewHas('attributes', fn ($attributes) => $attributes->count() === 2);
        $other = User::create(['first_name' => 'Other', 'last_name' => 'Agent',
            'email' => 'other-import@example.test', 'password' => 'test-password', 'user_role' => 'agent', 'is_active' => true]);
        $this->actingAs($other)->get('/panel/listing/'.$listing->id.'/edit')->assertForbidden();
    }

    public function test_reference_conflict_never_overwrites_an_existing_listing(): void
    {
        $importer = app(ListingImporter::class);
        $listing = $importer->import($this->bundle(), $this->agent->id)['listing'];
        $this->manifest['price'] = '1';
        try {
            $importer->import($this->bundle(), $this->agent->id);
            $this->fail('Expected reference conflict.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('reference', $exception->errors());
        }
        $this->assertSame('4250000.00', $listing->fresh()->price);
        $this->assertDatabaseCount('listings', $this->initialCount + 1);
        $this->assertCount(2, Storage::disk('uploads')->allFiles());
    }

    public function test_invalid_bundles_are_rejected_without_partial_writes(): void
    {
        $otherCity = City::create(['name' => 'Other', 'slug' => 'other']);
        $cases = [
            ['city_id' => $otherCity->id],
            ['category_id' => $this->category->parent_id],
            ['neighborhood_id' => 999999],
            ['longitude' => null],
            ['attributes' => [999999 => 'bad']],
            ['attributes' => [$this->area->id => -1]],
            ['attributes' => []],
            ['photos' => ['../outside.jpg']],
            ['photos' => ['https://example.test/a.jpg']],
            ['photos' => ['missing.jpg']],
            ['photos' => ['cover' => 'living-room.jpg']],
            ['photos' => []],
            ['video_url' => 'https://example.test/video'],
            ['price' => '1.234'],
            ['status' => 'active'],
            ['description' => '<script>alert("test")</script>'],
            ['description' => str_repeat(' ', 25)],
            ['video_url' => ['unexpected']],
        ];
        foreach ($cases as $override) {
            try {
                app(ListingImporter::class)->import($this->bundle(array_replace($this->manifest, $override)), $this->agent->id, publish: true);
                $this->fail('Expected validation failure for '.json_encode($override));
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
            $this->assertDatabaseCount('listings', $this->initialCount);
            $this->assertSame([], Storage::disk('uploads')->allFiles());
        }
    }

    public function test_failed_second_image_rolls_back_rows_and_removes_only_new_files(): void
    {
        Storage::disk('uploads')->put('uploads/listings/existing.txt', 'keep');
        $created = 0;
        ListingImage::creating(function () use (&$created) {
            if (++$created === 2) {
                throw new RuntimeException('Simulated image write failure');
            }
        });
        try {
            app(ListingImporter::class)->import($this->bundle(), $this->agent->id);
            $this->fail('Expected write failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated image write failure', $exception->getMessage());
        }
        $this->assertDatabaseCount('listings', $this->initialCount);
        $this->assertSame(['uploads/listings/existing.txt'], Storage::disk('uploads')->allFiles());
        $this->assertFileExists($this->bundleDirectory.'/balcony.jpg');
    }

    public function test_catalog_shows_actual_attribute_options_without_private_account_data(): void
    {
        $this->assertSame(0, Artisan::call('listings:import-catalog', ['--category' => $this->category->id]));
        $output = Artisan::output();
        $catalog = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $this->assertContains(true, array_column($catalog['attributes'], 'required_to_publish'));
        $options = array_merge(...array_column($catalog['attributes'], 'options'));
        $this->assertContains('2+1', array_column($options, 'name'));
        $this->assertStringNotContainsString('import@example.test', $output);
    }

    public function test_missing_agent_or_invalid_json_fails_cleanly(): void
    {
        $this->artisan('listings:import', ['file' => $this->bundle()])->assertFailed();
        File::put($this->bundleDirectory.'/broken.json', '{broken');
        $this->artisan('listings:import', ['file' => $this->bundleDirectory.'/broken.json', '--agent' => $this->agent->id])
            ->expectsOutputToContain(__('listing_import.invalid_json'))->assertFailed();
        $this->assertDatabaseCount('listings', $this->initialCount);
    }
}
