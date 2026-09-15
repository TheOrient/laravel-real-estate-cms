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
        Schema::create('attribute_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->string('name');
            $table->string('input_placeholder')->nullable();
            $table->timestamps();

            // Her özellik için her dilde sadece bir kayıt olabilir
            $table->unique(['attribute_id', 'language_id']);
            $table->index('attribute_id');
            $table->index('language_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_descriptions');
    }
};
