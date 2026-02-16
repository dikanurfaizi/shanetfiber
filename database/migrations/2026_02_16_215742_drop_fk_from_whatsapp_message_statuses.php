<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_message_statuses', function (Blueprint $table) {
            $table->dropForeign('whatsapp_message_statuses_wa_message_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_message_statuses', function (Blueprint $table) {
            $table->foreign('wa_message_id')
                ->references('wa_message_id')
                ->on('whatsapp_webhook_messages')
                ->onDelete('cascade');
        });
    }
};
