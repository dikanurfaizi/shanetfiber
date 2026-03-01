<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_message_media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('message_id')
                ->constrained('whatsapp_webhook_messages')
                ->cascadeOnDelete();

            $table->string('wa_media_id', 120)->unique();

            $table->string('file_name')->nullable();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('sha256', 100)->nullable();

            $table->string('storage_disk', 50)->default('local');
            $table->string('storage_path', 500);

            $table->enum('download_status', ['pending', 'downloaded', 'failed'])
                ->default('pending');

            $table->text('download_error')->nullable();
            $table->timestamp('downloaded_at')->nullable();

            $table->timestamps();

            $table->index('message_id');
            $table->index('download_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_message_media');
    }
};
