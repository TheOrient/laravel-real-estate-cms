<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Site içi kullanıcı–kullanıcı mesajlaşması (messages tablosu) kaldırıldı.
 * İletişim formu kayıtları contact_messages tablosunda kalır.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('messages');

        if (Schema::hasColumn('listings', 'allow_private_message')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->dropColumn('allow_private_message');
            });
        }

        DB::table('settings')->where('key', 'messaging_enabled')->delete();
    }

    public function down(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->unsignedBigInteger('listing_id');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
            $table->index(['listing_id', 'created_at']);
            $table->index(['sender_id', 'receiver_id']);
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->boolean('allow_private_message')->default(true)->after('allow_whatsapp');
        });
    }
};
