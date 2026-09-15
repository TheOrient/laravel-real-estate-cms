<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Listing;
use App\Models\User;
use Database\Seeders\SampleContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SeederSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_and_demo_seeders_refuse_to_replace_existing_content(): void
    {
        $this->seed();
        $before = [User::all()->toArray(), Listing::all()->toArray(), Blog::all()->toArray()];
        foreach ([null, SampleContentSeeder::class] as $seeder) {
            try {
                $seeder ? $this->seed($seeder) : $this->seed();
                $this->fail('Existing content must block installation seeders.');
            } catch (RuntimeException $exception) {
                $this->assertSame(__('installation.existing_database'), $exception->getMessage());
            }
            $this->assertSame($before, [User::all()->toArray(), Listing::all()->toArray(), Blog::all()->toArray()]);
        }
    }
}
