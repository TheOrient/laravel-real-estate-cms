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
        Schema::create('attribute_value_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
            $table->string('value');
            $table->timestamps();

            // Her özellik değeri için her dilde sadece bir kayıt olabilir
            $table->unique(['attribute_value_id', 'language_id'], 'attr_val_desc_unique');
            $table->index('attribute_value_id', 'attr_val_desc_attr_val_idx');
            $table->index('language_id', 'attr_val_desc_lang_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_value_descriptions');
    }
};
