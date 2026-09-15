<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the `blogs` table — language-independent fields only.
 *
 * Per-language fields (title, slug, body, excerpt, SEO meta) live in
 * `blog_descriptions` (see the sibling migration). This mirrors the
 * Page / PageDescription pattern already used in the project.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();

            // Author. Set null on user deletion so the post survives;
            // admins can reassign it from the panel.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Cover image (relative to public/uploads).
            $table->string('image')->nullable();

            // Lifecycle. status = 'draft' | 'published'.
            // is_active is the kill-switch (admin can hide a post without
            // touching its draft/published status).
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            // Analytics + scheduling.
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Hot path: "fetch latest published" appears on the home page,
            // blog index and sitemap. Compound index keeps that cheap.
            $table->index(['status', 'is_active', 'published_at']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
