<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanguageController as AdminLanguageController;
use App\Http\Controllers\Admin\ListingController as AdminListingController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\AttributeController as ApiAttributeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageResizeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\LocationController;
// Middleware aliases are now registered in bootstrap/app.php
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\User\UserListingController;
use App\Http\Controllers\User\UserPanelController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// for route localization check lang/tr/slug.php

// Sitemap route
// SEO & Sitemap Routes
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages'])->name('sitemap.pages');
Route::get('/sitemap-categories.xml', [SitemapController::class, 'categories'])->name('sitemap.categories');
Route::get('/sitemap-listings.xml', [SitemapController::class, 'listings'])->name('sitemap.listings');
Route::get('/sitemap-blogs.xml', [SitemapController::class, 'blogs'])->name('sitemap.blogs');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots.txt');

// Emlak portalı XML feed'leri — Emlakjet, Sahibinden, Hurriyet Emlak,
// Zingat / Hepsihome (generic) için ilan çekim URL'leri. Portal
// panelinde bu URL'leri kayıt ederek ilanları otomatik dağıtın.
Route::get('/feed/emlakjet.xml',    [\App\Http\Controllers\PortalFeedController::class, 'emlakjet'])->name('feed.emlakjet');
Route::get('/feed/sahibinden.xml',  [\App\Http\Controllers\PortalFeedController::class, 'sahibinden'])->name('feed.sahibinden');
Route::get('/feed/hurriyet.xml',    [\App\Http\Controllers\PortalFeedController::class, 'hurriyet'])->name('feed.hurriyet');
Route::get('/feed/generic.xml',     [\App\Http\Controllers\PortalFeedController::class, 'generic'])->name('feed.generic');

// Image resize route - using Glide for image manipulation and caching
Route::get('/img/{size}/{fit}/{path}', ImageResizeController::class)->where('path', '.*')->name('image.resize');

// Admin Routes (locale: varsayılan dil — URL'de dil öneki yok)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'IsActive', 'VersionCheck', 'admin', 'fixedLocaleAdmin'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Categories Management
    Route::resource('categories', AdminCategoryController::class);

    // Translation Management
    Route::post('translations/translate', [\App\Http\Controllers\TranslationController::class, 'translate'])->name('translations.translate');

    // Language Management (only list & edit existing languages)
    Route::resource('languages', AdminLanguageController::class)->only(['index', 'edit', 'update']);

    // Attribute Management Routes
    Route::resource('attributes', AttributeController::class);

    // Category Attribute Management Routes
    Route::get('category-attributes', [AttributeController::class, 'showCategoryAttributes'])->name('category.attributes');
    Route::get('categories/{category}/attributes', [AttributeController::class, 'getCategoryAttributes'])->name('categories.attributes.get');
    Route::post('categories/{category}/assign-attributes', [AttributeController::class, 'assignToCategory'])->name('categories.attributes.assign');

    // User Management Routes
    Route::resource('users', UserController::class);
    Route::delete('users/{user}/force', [UserController::class, 'forceDestroy'])->name('users.force-destroy');
    Route::patch('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');

    // Listing Management Routes
    Route::get('listings', [AdminListingController::class, 'index'])->name('listings.index');
    Route::get('listings/{listing}', [AdminListingController::class, 'show'])->name('listings.show');
    Route::post('listings/{listing}/approve', [AdminListingController::class, 'approve'])->name('listings.approve');
    Route::post('listings/{listing}/reject', [AdminListingController::class, 'reject'])->name('listings.reject');
    Route::post('listings/{listing}/activate', [AdminListingController::class, 'activate'])->name('listings.activate');
    Route::post('listings/{listing}/deactivate', [AdminListingController::class, 'deactivate'])->name('listings.deactivate');
    Route::post('listings/{listing}/toggle-featured', [AdminListingController::class, 'toggleFeatured'])->name('listings.toggle-featured');
    Route::delete('listings/{listing}', [AdminListingController::class, 'destroy'])->name('listings.destroy');
    Route::delete('listings/{listing}/force', [AdminListingController::class, 'forceDestroy'])->name('listings.force-destroy');
    Route::patch('listings/{listing}/restore', [AdminListingController::class, 'restore'])->name('listings.restore');

    // Location Management Routes
    Route::prefix('locations')->name('locations.')->group(function () {
        // Cities
        Route::resource('cities', \App\Http\Controllers\Admin\CityController::class);

        // Districts
        Route::resource('districts', \App\Http\Controllers\Admin\DistrictController::class);
        Route::get('cities/{city}/districts', [\App\Http\Controllers\Admin\DistrictController::class, 'byCity'])->name('districts.by-city');

        // Neighborhoods
        Route::resource('neighborhoods', \App\Http\Controllers\Admin\NeighborhoodController::class);
        Route::get('districts/{district}/neighborhoods', [\App\Http\Controllers\Admin\NeighborhoodController::class, 'byDistrict'])->name('neighborhoods.by-district');
    });

    // Settings Management Routes
    Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::post('settings/clear-cache', [\App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('settings.clear-cache');

    // Page Management Routes
    Route::resource('pages', AdminPageController::class);

    // Contact Messages Management Routes
    Route::resource('contact-messages', AdminContactMessageController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::post('contact-messages/bulk-action', [AdminContactMessageController::class, 'bulkAction'])->name('contact-messages.bulk-action');

    // FAQ Management Routes
    Route::resource('faq-categories', \App\Http\Controllers\Admin\FaqCategoryController::class);
    Route::resource('faqs', \App\Http\Controllers\Admin\FaqController::class);

    // Blog Management Routes
    Route::resource('blogs', AdminBlogController::class)->except(['show']);

    // AI endpoints (rate-limited to keep stuck UIs from burning tokens)
    Route::post('ai/generate-listing-description',
        [\App\Http\Controllers\Admin\AIController::class, 'generateListingDescription']
    )->middleware('throttle:30,1')->name('ai.generate-listing-description');

    // Social media manual trigger (Task #10)
    Route::post('listings/{listing}/social-share',
        [\App\Http\Controllers\Admin\SocialShareController::class, 'share']
    )->name('listings.social-share');

    // PDF flyer (Task #12)
    Route::get('listings/{listing}/flyer',
        [\App\Http\Controllers\Admin\FlyerController::class, 'download']
    )->name('listings.flyer');

});

// Ön yüz: tek dil, URL önekinde dil kodu yok
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category/{slug}', [CategoryController::class, 'show'])
    ->where('slug', '.*')
    ->name('categories.show');
Route::get('/listing/{slug}', [ListingController::class, 'show'])->name('listings.show');
Route::get('/all', [CategoryController::class, 'showAll'])->name('categories.show.all');

Route::get('/page/{slug}', [PageController::class, 'show'])->name('pages.show');
Route::get('/faq', [\App\Http\Controllers\FaqController::class, 'index'])->name('faq.index');

// Blog routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Locale switch (session-based). The user lands back where they came from.
Route::get('/locale/{code}', [\App\Http\Controllers\LocaleController::class, 'switch'])
    ->where('code', '[a-z]{2}')
    ->name('locale.switch');

// Public chatbot endpoint — throttled hard to keep token usage sane.
Route::post('/chatbot/reply', [\App\Http\Controllers\ChatbotController::class, 'reply'])
    ->middleware('throttle:20,1')
    ->name('chatbot.reply');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/api/cities/{city}/districts', [LocationController::class, 'getDistrictsByCity'])->name('api.districts.by.city');
Route::get('/api/districts/{district}/neighborhoods', [LocationController::class, 'getNeighborhoodsByDistrict'])->name('api.neighborhoods.by.district');
Route::get('/api/categories/{category}/attributes', [ApiAttributeController::class, 'getCategoryAttributes'])->name('api.categories.attributes');
Route::get('/api/categories/{category}/filterable-attributes', [ApiAttributeController::class, 'getFilterableAttributes'])->name('api.categories.filterable-attributes');

Route::get('/password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
});

Route::middleware(['auth', 'IsActive', 'VersionCheck'])->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/')->with('success', 'Email adresiniz başarıyla doğrulandı!');
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Doğrulama linki email adresinize gönderildi!');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

Route::middleware(['auth', 'IsActive', 'VersionCheck'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::prefix('panel')->name('user.')->group(function () {
        Route::post('translations/translate', [\App\Http\Controllers\TranslationController::class, 'translate'])->name('translations.translate');

        // AI helper for the listing-create flow (description generator).
        Route::post('ai/generate-listing-description',
            [\App\Http\Controllers\User\AIController::class, 'generateListingDescription']
        )->middleware('throttle:30,1')->name('ai.generate-listing-description');

        Route::get('/', [UserPanelController::class, 'index'])->name('dashboard');
        Route::get('/profile', [UserPanelController::class, 'profile'])->name('profile');
        Route::post('/profile', [UserPanelController::class, 'updateProfile'])->name('profile.update');
        Route::get('/my-listings', [UserPanelController::class, 'myListings'])->name('listings.my');
        Route::post('/listing/{listing}/images/ajax', [UserListingController::class, 'uploadImagesAjax'])->name('listings.images.upload.ajax');
        Route::delete('/listing-images/{image}', [UserListingController::class, 'deleteImage'])->name('listings.images.delete');

        Route::prefix('listing-create')->name('listings.')->middleware(\App\Http\Middleware\EnsureEmailIsVerified::class)->group(function () {
            Route::get('/', [UserListingController::class, 'create'])->name('create');
            Route::post('/step1', [UserListingController::class, 'storeStep1'])->name('create.step1.store');
            Route::get('/step2', [UserListingController::class, 'createStep2'])->name('create.step2');
            Route::post('/step2', [UserListingController::class, 'storeStep2'])->name('create.step2.store');
            Route::get('/step3', [UserListingController::class, 'createStep3'])->name('create.step3');
            Route::post('/step3', [UserListingController::class, 'storeStep3'])->name('create.step3.store');
            Route::get('/{listing}/step4', [UserListingController::class, 'createStep4'])->name('create.step4');
            Route::post('/{listing}/step4', [UserListingController::class, 'storeStep4'])->name('create.step4.store');
            Route::get('/{listing}/images', [UserListingController::class, 'images'])->name('images');
            Route::post('/{listing}/images', [UserListingController::class, 'uploadImages'])->name('images.store');
        });

        Route::prefix('listing')->name('listings.')->group(function () {
            Route::get('/{listing}/edit', [UserListingController::class, 'edit'])->name('edit');
            Route::put('/{listing}', [UserListingController::class, 'update'])->name('update');
            Route::delete('/{listing}', [UserListingController::class, 'destroy'])->name('destroy');

            // Danışman paneli ilan durumu yönetimi.
            Route::post('/{listing}/publish', [UserListingController::class, 'publish'])->name('publish');
            Route::post('/{listing}/unpublish', [UserListingController::class, 'unpublish'])->name('unpublish');
            Route::post('/{listing}/toggle-active', [UserListingController::class, 'toggleActive'])->name('toggle-active');
            Route::post('/{listing}/toggle-featured', [UserListingController::class, 'toggleFeatured'])->name('toggle-featured');
        });
    });
});
