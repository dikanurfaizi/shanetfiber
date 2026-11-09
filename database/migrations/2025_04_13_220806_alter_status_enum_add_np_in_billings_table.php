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
        DB::statement("ALTER TABLE `billings` MODIFY `status` ENUM('BL', 'LS', 'PL', 'NP') NOT NULL DEFAULT 'BL';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Balikin ke enum awal kalau rollback
        DB::statement("ALTER TABLE `billings` MODIFY `status` ENUM('BL', 'LS', 'PL') NOT NULL DEFAULT 'BL';");
    }
};
