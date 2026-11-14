<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('message_events') && Schema::hasColumn('message_events', 'message_id')) {
            DB::statement('ALTER TABLE message_events MODIFY message_id VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('message_events') && Schema::hasColumn('message_events', 'message_id')) {
            DB::statement('ALTER TABLE message_events MODIFY message_id VARCHAR(50) NULL');
        }
    }
};
