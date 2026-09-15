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
        Schema::create('listing_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->onDelete('cascade'); // listing reference
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // optional user reference
            $table->string('reporter_name')->nullable(); // for non-registered users
            $table->string('reporter_email'); // contact email
            $table->string('reason'); // report reason
            $table->text('description')->nullable(); // detailed description
            $table->string('status')->default('pending'); // pending, reviewed, resolved
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_reports');
    }
};
