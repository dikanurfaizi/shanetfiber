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
        // rename table
        Schema::rename('onus', 'onts');

        // rename columns di tabel onts
        Schema::table('onts', function (Blueprint $table) {
            if (Schema::hasColumn('onts', 'onu_index')) {
                $table->renameColumn('onu_index', 'ont_index');
            }
            if (Schema::hasColumn('onts', 'onu_status')) {
                $table->renameColumn('onu_status', 'ont_status');
            }
            // tambahin sesuai daftar kolom yang ada "onu"
        });

        // contoh: kalau ada foreign key di tabel lain
        Schema::table('olt_logs', function (Blueprint $table) {
            if (Schema::hasColumn('olt_logs', 'onu_id')) {
                $table->renameColumn('onu_id', 'ont_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('onts', 'onus');

        Schema::table('onus', function (Blueprint $table) {
            if (Schema::hasColumn('onus', 'ont_index')) {
                $table->renameColumn('ont_index', 'onu_index');
            }
            if (Schema::hasColumn('onus', 'ont_status')) {
                $table->renameColumn('ont_status', 'onu_status');
            }
        });

        Schema::table('olt_logs', function (Blueprint $table) {
            if (Schema::hasColumn('olt_logs', 'ont_id')) {
                $table->renameColumn('ont_id', 'onu_id');
            }
        });
    }
};
