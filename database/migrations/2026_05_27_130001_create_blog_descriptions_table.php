<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `blog_descriptions` — per-language content for each blog post.
 *
 * Follows the same shape as `page_descriptions`. The slug is unique
 * per language (compound unique) rather than globally unique so the
 * Turkish and English versions of the same post can share a slug
 * if the operator chooses to.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();

            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();   // Short preview shown on listings
            $table->longText('body')->nullable();  // Full HTML content

            // Per-language SEO. meta_description falls back to excerpt
            // at render time when empty.
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            $table->timestamps();

            // One post can only have one description per language.
            $table->unique(['blog_id', 'language_id']);
            // Lookup by slug+language is how the frontend resolves
            // /blog/{slug}; index covers it.
            $table->unique(['language_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_descriptions');
    }
};
