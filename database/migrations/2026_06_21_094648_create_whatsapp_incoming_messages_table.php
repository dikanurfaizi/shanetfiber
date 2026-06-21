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
        Schema::create('whatsapp_incoming_messages', function (Blueprint $table) {
            $table->id();
            $table->string('trace_id')->nullable()->index();
            $table->string('phone_number')->index();
            // customer_id dibuat nullable karena pelanggan bisa saja belum terdaftar
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->text('message_text')->nullable();
            $table->string('device_id')->nullable();
            $table->string('resolved_keyword_slug')->nullable();
            $table->timestamps();

            // Opsional: Jika Anda memiliki tabel customers dan ingin menambahkan foreign key constraint
            // $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_incoming_messages');
    }
};
