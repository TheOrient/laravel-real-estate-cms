<?php

namespace Tests\Feature;

use App\Models\BlogDescription;
use App\Models\Listing;
use App\Models\ListingDescription;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_login_attempts_are_rate_limited(): void
    {
        config(['auth.login_rate_limit' => 3]);
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post('/login', ['email' => 'missing@example.test', 'password' => 'incorrect'])->assertRedirect();
        }
        $this->post('/login', ['email' => 'missing@example.test', 'password' => 'incorrect'])
            ->assertStatus(429)->assertHeader('Retry-After');
    }

    public function test_admin_and_agent_panels_are_protected_and_render_for_the_office(): void
    {
        $this->seed();
        foreach (['/admin', '/admin/listings', '/admin/blogs', '/admin/settings', '/panel/my-listings'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }

        $admin = User::where('user_role', 'admin')->firstOrFail();
        $this->actingAs($admin);
        foreach (['/admin', '/admin/listings', '/admin/blogs', '/admin/settings', '/admin/users', '/panel/my-listings'] as $path) {
            $this->get($path)->assertOk();
        }
        $listing = Listing::firstOrFail();
        $this->get('/admin/listings/'.$listing->id)->assertRedirect(route('listings.show', $listing->slug));
        foreach ([$listing->id, $listing->title, $admin->first_name] as $search) {
            $this->get('/admin/listings?'.http_build_query(['search' => $search]))->assertOk()->assertSee($listing->title);
        }
        $this->get('/panel/listing/'.$listing->id.'/edit')->assertOk();
    }

    public function test_publication_critical_routes_and_locales_are_available(): void
    {
        $this->seed();

        foreach ([
            '/',
            '/all',
            '/blog',
            '/faq',
            '/page/hakkimizda',
            '/page/iletisim',
            '/category/emlak/konut/satilik-daire',
            '/sitemap.xml',
            '/sitemap-pages.xml',
            '/sitemap-categories.xml',
            '/sitemap-listings.xml',
            '/sitemap-blogs.xml',
            '/robots.txt',
        ] as $path) {
            $this->get($path)->assertOk();
        }

        $this->get('/page/hakkimizda?lang=en')
            ->assertOk()
            ->assertSee('About Us');

        $this->get('/sitemap-categories.xml')
            ->assertOk()
            ->assertSee('/category/emlak/konut/satilik-daire', false);

        $this->get('/login')
            ->assertOk()
            ->assertSee('noindex, nofollow', false);
    }

    public function test_seeded_public_content_is_clearly_identified_as_fictional_demo_data(): void
    {
        $this->seed();

        $listingTitles = ListingDescription::pluck('title');
        $blogTitles = BlogDescription::pluck('title');

        $this->assertNotEmpty($listingTitles);
        $this->assertNotEmpty($blogTitles);
        $this->assertTrue($listingTitles->every(fn (string $title) => str_starts_with($title, '[DEMO]')));
        $this->assertTrue($blogTitles->every(fn (string $title) => str_starts_with($title, '[DEMO]')));
        $this->assertSame('demo@example.test', Setting::get('contact_email'));
    }
}
