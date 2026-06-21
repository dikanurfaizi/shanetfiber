<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique(); // Unique karena 1 nomor hanya punya 1 status session aktif
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('device_id')->nullable();
            $table->enum('chat_mode', ['bot', 'human'])->default('bot')->index();
            $table->timestamp('last_human_activity_at')->nullable(); // Mencatat kapan chat admin terakhir aktif untuk deteksi 30 menit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_chat_sessions');
    }
};
