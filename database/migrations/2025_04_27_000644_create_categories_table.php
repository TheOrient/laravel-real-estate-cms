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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            // Çevrilmesi gereken sütunlar (name, slug, short_description, description) category_descriptions tablosuna taşındı
            $table->string('image')->nullable(); // image local image uploaded to public/uploads/categories
            $table->string('icon')->nullable(); // icon url ( prefered remixicon )
            $table->string('parent_id')->nullable(); // parent category id
            $table->boolean('is_active')->default(true);
            $table->boolean('is_filterable')->default(false); // Whether this category can be filtered
            $table->integer('listings_count')->default(0); // Store the count directly in the table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
