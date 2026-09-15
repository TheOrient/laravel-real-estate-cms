<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Banner name for identification
            $table->text('html_code'); // HTML code to display
            $table->enum('position', [
                'global_after_header',
                'global_before_footer',
                'home_after_featured',
                'home_after_urgent',
                'category_top',
                'category_sidebar',
                'category_bottom',
                'listing_detail_top',
                'listing_detail_sidebar',
                'listing_detail_bottom'
            ]); // Position where banner will be displayed
            $table->json('category_ids')->nullable(); // JSON array of category IDs (only parent categories)
            $table->enum('visibility', ['all', 'members_only', 'guests_only'])->default('all'); // Who can see this banner
            $table->integer('order')->default(0); // Display order
            $table->boolean('is_active')->default(true); // Active status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
