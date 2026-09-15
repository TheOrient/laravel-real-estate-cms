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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_category_id')->constrained('faq_categories')->onDelete('cascade');
            $table->string('question'); // FAQ question/title
            $table->text('answer'); // FAQ answer/description
            $table->integer('sort_order')->default(0); // Display order
            $table->boolean('is_active')->default(true); // Active status
            $table->timestamps();

            $table->index('faq_category_id');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
