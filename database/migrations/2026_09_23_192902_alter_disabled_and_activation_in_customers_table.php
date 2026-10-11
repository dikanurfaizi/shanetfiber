<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // -------------------------------------------------------------------------
        // 1. PENANGANAN KOLOM IS_ACTIVE (Diletakkan TEPAT AFTER contact)
        // -------------------------------------------------------------------------
        if (Schema::hasColumn('customers', 'disabled')) {
            // A. Tambahkan 'is_active' tepat setelah kolom 'contact'
            DB::statement("ALTER TABLE `customers` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Active, 0: Disabled' AFTER `contact` ");

            // B. Migrasi Data: disabled = 'false' -> is_active = 1, disabled = 'true' -> is_active = 0
            DB::statement("UPDATE `customers` SET `is_active` = 1 WHERE `disabled` = 'false'");
            DB::statement("UPDATE `customers` SET `is_active` = 0 WHERE `disabled` = 'true'");

            // C. Hapus kolom lama 'disabled'
            DB::statement("ALTER TABLE `customers` DROP COLUMN `disabled` ");
        }

        // -------------------------------------------------------------------------
        // 2. PENANGANAN KOLOM ACTIVATION (Diletakkan TEPAT AFTER is_active)
        // -------------------------------------------------------------------------
        if (Schema::hasColumn('customers', 'activation')) {
            // A. Tambahkan temporary column 'activation_temp' tepat setelah kolom 'is_active'
            DB::statement("ALTER TABLE `customers` ADD COLUMN `activation_temp` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Activated, 0: Not Activated' AFTER `is_active` ");

            // B. Migrasi Data: activation = 'true' -> 1, activation = 'false' -> 0
            DB::statement("UPDATE `customers` SET `activation_temp` = 1 WHERE `activation` = 'true'");
            DB::statement("UPDATE `customers` SET `activation_temp` = 0 WHERE `activation` = 'false'");

            // C. Hapus kolom 'activation' lama, lalu rename 'activation_temp' menjadi 'activation'
            DB::statement("ALTER TABLE `customers` DROP COLUMN `activation` ");
            DB::statement("ALTER TABLE `customers` CHANGE COLUMN `activation_temp` `activation` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Activated, 0: Not Activated'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback activation TINYINT -> ENUM
        if (Schema::hasColumn('customers', 'activation')) {
            DB::statement("ALTER TABLE `customers` ADD COLUMN `activation_enum` ENUM('false', 'true') NOT NULL DEFAULT 'true' AFTER `contact` ");
            DB::statement("UPDATE `customers` SET `activation_enum` = 'true' WHERE `activation` = 1");
            DB::statement("UPDATE `customers` SET `activation_enum` = 'false' WHERE `activation` = 0");
            DB::statement("ALTER TABLE `customers` DROP COLUMN `activation` ");
            DB::statement("ALTER TABLE `customers` CHANGE COLUMN `activation_enum` `activation` ENUM('false', 'true') NOT NULL DEFAULT 'true'");
        }

        // Rollback is_active -> disabled ENUM
        if (Schema::hasColumn('customers', 'is_active')) {
            DB::statement("ALTER TABLE `customers` ADD COLUMN `disabled` ENUM('false', 'true') NOT NULL DEFAULT 'false' AFTER `activation` ");
            DB::statement("UPDATE `customers` SET `disabled` = 'false' WHERE `is_active` = 1");
            DB::statement("UPDATE `customers` SET `disabled` = 'true' WHERE `is_active` = 0");
            DB::statement("ALTER TABLE `customers` DROP COLUMN `is_active` ");
        }
    }
};
