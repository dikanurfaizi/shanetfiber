<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_webhook_messages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('conversation_id')->index();

            // Idempotency
            $table->string('wa_message_id', 120)->unique();
            $table->uuid('trace_id')->nullable();

            $table->enum('direction', ['incoming', 'outgoing'])->index();

            $table->string('from_number', 20);
            $table->string('to_number', 20)->nullable();

            $table->string('type', 30);
            $table->text('text_body')->nullable();

            $table->timestamp('timestamp_wa')->nullable();

            // Lifecycle timestamps
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // Cache status
            $table->string('last_status', 30)->nullable()->index();

            $table->json('payload');

            $table->timestamps();

            $table->foreign('conversation_id')
                ->references('id')
                ->on('whatsapp_conversations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_webhook_messages');
    }
};
