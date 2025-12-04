<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            // ENUM scope: registered, unregistered, both
            $table->enum('message_scope', ['registered', 'unregistered', 'both'])
                ->default('both')
                ->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropColumn('message_scope');
        });
    }
};
