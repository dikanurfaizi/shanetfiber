<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE receipt_items
            MODIFY COLUMN type 
            ENUM('package', 'addon', 'discount')
            NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE receipt_items
            MODIFY COLUMN type 
            ENUM('package', 'addon')
            NOT NULL
        ");
    }
};
