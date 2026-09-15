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
        Schema::create('category_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Her kategori için her dilde sadece bir kayıt olabilir
            $table->unique(['category_id', 'language_id']);
            $table->index('category_id');
            $table->index('language_id');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_descriptions');
    }
};
