<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oid_mappings', function (Blueprint $table) {
            // hapus kolom OID lama kalau tidak dipakai lagi
            $table->dropColumn([
                'oid_name',
                'oid_tx',
                'oid_rx',
                'oid_status',
                'oid_mac',
                'oid_temp',
            ]);

            // tambahkan kolom opsional untuk info vendor / model
            $table->string('model', 100)->nullable()->after('vendor');
            $table->string('description', 255)->nullable()->after('model');
        });
    }

    public function down(): void
    {
        Schema::table('oid_mappings', function (Blueprint $table) {
            // rollback: tambahkan kembali kolom lama
            $table->string('oid_name', 255)->nullable();
            $table->string('oid_tx', 255)->nullable();
            $table->string('oid_rx', 255)->nullable();
            $table->string('oid_status', 255)->nullable();
            $table->string('oid_mac', 255)->nullable();
            $table->string('oid_temp', 255)->nullable();

            $table->dropColumn(['model', 'description']);
        });
    }
};
