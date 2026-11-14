<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modifikasi tabel billing_cycles
        if (Schema::hasTable('billing_cycles')) {
            Schema::table('billing_cycles', function (Blueprint $table) {
                if (!Schema::hasColumn('billing_cycles', 'is_active')) {
                    $table->boolean('is_active')->default(false);
                }
            });
            DB::statement('ALTER TABLE billing_cycles MODIFY is_active TINYINT(1) NOT NULL DEFAULT 0');
        }

        // 2. Modifikasi tabel paket_categories
        if (Schema::hasTable('paket_categories')) {
            Schema::table('paket_categories', function (Blueprint $table) {
                if (!Schema::hasColumn('paket_categories', 'is_active')) {
                    $table->boolean('is_active')->default(false);
                }
            });
            DB::statement('ALTER TABLE paket_categories MODIFY is_active TINYINT(1) NOT NULL DEFAULT 0');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert default value kembali ke true jika rollback
        if (Schema::hasTable('billing_cycles') && Schema::hasColumn('billing_cycles', 'is_active')) {
            DB::statement('ALTER TABLE billing_cycles MODIFY is_active TINYINT(1) NOT NULL DEFAULT 1');
        }

        if (Schema::hasTable('paket_categories') && Schema::hasColumn('paket_categories', 'is_active')) {
            DB::statement('ALTER TABLE paket_categories MODIFY is_active TINYINT(1) NOT NULL DEFAULT 1');
        }
    }
};
