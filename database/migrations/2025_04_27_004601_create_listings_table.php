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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->decimal('price', 12, 2)->nullable();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
            $table->foreignId('district_id')->constrained()->onDelete('cascade');
            $table->foreignId('neighborhood_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('status')->default('active'); // active, pending, sold, expired
            $table->boolean('is_featured')->default(false);
            $table->string('location')->nullable(); // Formatted location string
            $table->decimal('latitude', 10, 8)->nullable(); // Latitude coordinate
            $table->decimal('longitude', 11, 8)->nullable(); // Longitude coordinate
            $table->timestamp('expires_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->string('image')->nullable(); // image name in public/uploads/listings/ example name 2025/04/27/1234567890.jpg
            $table->boolean('is_active')->default(true);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_sent_to_approver')->default(false);
            $table->timestamp('sent_to_approver_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('allow_whatsapp')->default(false); // WhatsApp contact permission
            $table->boolean('allow_private_message')->default(true); // Private message permission
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
