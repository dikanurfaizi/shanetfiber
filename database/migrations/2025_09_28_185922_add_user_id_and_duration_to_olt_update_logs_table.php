<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('olt_update_logs', function (Blueprint $table) {
            $table->string('username')->nullable()->after('status');
            $table->double('duration', 8, 2)->nullable()->after('onu_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('olt_update_logs', function (Blueprint $table) {
            $table->dropColumn(['username', 'duration']);
        });
    }
};
