<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE billing_items 
            MODIFY COLUMN item_type 
            ENUM('addon','product','service','manual','package','discount') 
            NOT NULL;
        ");
    }

    public function down(): void
    {
        // Kembalikan ke enum sebelumnya jika perlu
        DB::statement("
            ALTER TABLE billing_items 
            MODIFY COLUMN item_type 
            ENUM('addon','product','service','manual') 
            NOT NULL;
        ");
    }
};
