<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();

            $table->string('channel', 30)->default('whatsapp');
            $table->string('customer_number', 20)->index();

            // Locking system
            $table->unsignedBigInteger('locked_by_admin_id')->nullable()->index();
            $table->timestamp('locked_at')->nullable();

            // State
            $table->boolean('has_admin_reply')->default(false);
            $table->boolean('has_unread')->default(true);

            $table->string('status', 20)->default('open')->index();

            $table->timestamp('last_message_at')->nullable()->index();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->foreign('locked_by_admin_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversations');
    }
};
