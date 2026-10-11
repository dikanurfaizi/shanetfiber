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
        // 1. Rename nama kolom dari 'disabled' menjadi 'is_active' dan ubah tipenya ke TINYINT(1) / BOOLEAN
        DB::statement("ALTER TABLE `banks` CHANGE COLUMN `disabled` `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Active, 0: Inactive'");

        // 2. Balik nilainya (karena logika disabled terbalik dengan is_active)
        // Nilai ENUM 'false' (index/value lama) kini menjadi 1 (Active)
        // Nilai ENUM 'true' kini menjadi 0 (Inactive)
        // Catatan: MySQL saat memutasi ENUM 'false' ke TINYINT biasanya otomatis terpetakan,
        // namun untuk memastikan 100% tepat:
        DB::table('banks')->where('is_active', 0)->update(['is_active' => 1]); // Yang tadi 'false' (artinya aktif) di-set ke 1
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `banks` CHANGE COLUMN `is_active` `disabled` ENUM('false', 'true') NOT NULL DEFAULT 'false'");
    }
};
