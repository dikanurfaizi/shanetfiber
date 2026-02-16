<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_message_statuses', function (Blueprint $table) {
            $table->id();

            $table->string('wa_message_id', 120)->index();

            $table->string('status', 30)->index();
            $table->timestamp('status_at')->nullable();

            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->foreign('wa_message_id')
                ->references('wa_message_id')
                ->on('whatsapp_webhook_messages')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_message_statuses');
    }
};
