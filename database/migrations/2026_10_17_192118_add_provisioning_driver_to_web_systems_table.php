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
        Schema::table('websystems', function (Blueprint $table) {
            // Menambahkan kolom provisioning_driver dengan default 'both' (Dual Provisioning)
            $table->enum('provisioning_driver', ['radius', 'mikrotik', 'both'])
                ->default('both')
                ->after('billing_auto_day'); // Sesuaikan posisi penempatan kolom di tabel web_systems
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websystems', function (Blueprint $table) {
            $table->dropColumn('provisioning_driver');
        });
    }
};
