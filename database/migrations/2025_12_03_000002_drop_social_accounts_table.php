<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('social_accounts')) {
            Schema::drop('social_accounts');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tablo tamamen kaldırıldığı için down metodunda yeniden oluşturmuyoruz.
    }
};
