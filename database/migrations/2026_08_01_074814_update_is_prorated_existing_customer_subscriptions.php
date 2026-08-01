<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Menandai record sebagai is_prorated = 1 (true) sesuai kondisi harga dan tanggal
        DB::statement("
            UPDATE customer_subscriptions cs
            JOIN packages p ON cs.package_id = p.id
            SET cs.is_prorated = 1
            WHERE cs.start_date >= '2026-07-15'
              AND p.price != cs.price_final
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: kembalikan is_prorated = 0 untuk data terkait
        DB::statement("
            UPDATE customer_subscriptions cs
            JOIN packages p ON cs.package_id = p.id
            SET cs.is_prorated = 0
            WHERE cs.start_date >= '2026-07-15'
              AND p.price != cs.price_final
        ");
    }
};
