<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Ubah discount_amount agar bisa NEGATIF
         * Asumsi awal: DECIMAL(15,2) UNSIGNED
         */
        DB::statement("
            ALTER TABLE billings
            MODIFY discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0
        ");
    }

    public function down(): void
    {
        /**
         * 1. Normalisasi data negatif → positif
         *    Supaya aman saat diubah ke UNSIGNED
         */
        DB::statement("
        UPDATE billings
        SET discount_amount = ABS(discount_amount)
        WHERE discount_amount < 0
    ");

        /**
         * 2. Ubah kolom kembali ke UNSIGNED
         */
        DB::statement("
        ALTER TABLE billings
        MODIFY discount_amount DECIMAL(15,2) UNSIGNED NOT NULL DEFAULT 0
    ");
    }
};
