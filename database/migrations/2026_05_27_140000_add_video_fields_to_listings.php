<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add video support to listings.
 *
 * Two complementary fields: an external embed URL (YouTube / Vimeo —
 * the common case) and an optional self-hosted file. Either may be
 * set; the detail view picks whichever is present.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('image');   // YouTube / Vimeo link
            $table->string('video_file')->nullable()->after('video_url'); // public/uploads/listings/videos/…
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'video_file']);
        });
    }
};
