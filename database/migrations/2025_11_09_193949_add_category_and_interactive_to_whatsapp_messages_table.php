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
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            // Tambahkan kolom baru setelah kolom slug
            if (!Schema::hasColumn('whatsapp_messages', 'category')) {
                $table->enum('category', [
                    'BROADCAST',
                    'INTERACTIVE',
                    'SYSTEM',
                ])->default('SYSTEM')->after('disabled');
            }

            if (!Schema::hasColumn('whatsapp_messages', 'is_interactive')) {
                $table->boolean('is_interactive')
                    ->default(false)
                    ->after('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_messages', 'category')) {
                $table->dropColumn('category');
            }

            if (Schema::hasColumn('whatsapp_messages', 'is_interactive')) {
                $table->dropColumn('is_interactive');
            }
        });
    }
};
