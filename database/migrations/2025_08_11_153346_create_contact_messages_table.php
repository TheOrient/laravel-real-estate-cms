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
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contact person name
            $table->string('email'); // Contact email
            $table->string('phone')->nullable(); // Phone number (optional)
            $table->string('subject'); // Message subject
            $table->text('message'); // Message content
            $table->string('status')->default('new'); // new, read, replied, archived
            $table->timestamp('read_at')->nullable(); // When message was read
            $table->text('admin_notes')->nullable(); // Admin internal notes
            $table->string('ip_address')->nullable(); // IP address for security
            $table->timestamps();

            // Indexes for performance
            $table->index(['status', 'created_at']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
