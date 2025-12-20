<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // 1. Replace cancelled → terminated
        DB::table('customer_subscriptions')
            ->where('status', 'cancelled')
            ->update(['status' => 'terminated']);

        // 2. Modify ENUM using raw SQL
        DB::statement("
            ALTER TABLE customer_subscriptions
            MODIFY COLUMN status ENUM('active','paused','terminated')
            NOT NULL DEFAULT 'active'
        ");
    }

    public function down(): void
    {
        // Restore old ENUM
        DB::statement("
            ALTER TABLE customer_subscriptions
            MODIFY COLUMN status ENUM('active','paused','cancelled','terminated')
            NOT NULL DEFAULT 'active'
        ");
    }
};
