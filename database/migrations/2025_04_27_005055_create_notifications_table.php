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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type'); // listing_approved, listing_rejected, new_listing, new_report, new_message, custom
            $table->string('target')->default('user'); // user or admin
            $table->boolean('is_read')->default(false);
            $table->boolean('is_important')->default(false);
            $table->string('icon')->nullable(); // Icon class (ri-check-line, ri-close-line, etc.)
            $table->json('action_links')->nullable(); // Array of action links with titles and URLs
            $table->string('related_type')->nullable(); // Model type (Listing, User, ListingReport)
            $table->unsignedBigInteger('related_id')->nullable(); // Related model ID
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Recipient user (null for admin notifications)
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // Who created this notification
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['target', 'is_read', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
