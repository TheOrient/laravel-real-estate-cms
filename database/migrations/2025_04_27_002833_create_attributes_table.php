<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constants\ListingAttributeDisplayTypeConstant;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            // Name and input_placeholder are now in attribute_descriptions table
            // how to display on search page
            $table->enum('display_type_search',ListingAttributeDisplayTypeConstant::getAllTypes())->default(
                ListingAttributeDisplayTypeConstant::SELECT
            );
            // how to display on listing create/edit pages
            $table->enum('display_type_user_panel', ListingAttributeDisplayTypeConstant::getAllTypes())->default(
                ListingAttributeDisplayTypeConstant::SELECT
            );
            // Whether to use in category filters
            $table->boolean('is_filterable')->default(false);
            // Whether to show in listing detail
            $table->boolean('show_in_listing')->default(true);
            // Whether it's required when creating a listing
            $table->boolean('is_required')->default(false);
            // Whether multiple values can be selected (for checkbox)
            $table->boolean('allow_multiple')->default(false);
            $table->integer('min_value')->nullable();
            $table->integer('max_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
