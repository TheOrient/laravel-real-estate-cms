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
        Schema::create('listing_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->string('image'); // image name in public/uploads/listings/ example name 2025/05/27/default.jpg
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            // is_active field is added via separate migration (2025_11_07_195227_add_is_active_to_listing_images_table.php)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_images');
    }
};
