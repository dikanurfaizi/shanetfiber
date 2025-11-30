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
        Schema::table('whatsapp_gateway_accounts', function (Blueprint $table) {
            // Tambah unique index
            // nama index dibuat manual supaya konsisten & mudah di-drop
            $table->unique('meta_phone_number_id', 'whatsapp_accounts_phone_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_gateway_accounts', function (Blueprint $table) {
            // Hapus unique index jika rollback
            $table->dropUnique('whatsapp_accounts_phone_unique');
        });
    }
};
