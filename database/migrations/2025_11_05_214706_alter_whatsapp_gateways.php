<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('whatsapp_gateways', function (Blueprint $table) {
            $table->boolean('auth_enabled')->default(true)->after('basic_auth_password');
        });

        // Migrasikan nilai lama: auth_disabled = "true" → auth_enabled = 0
        // default jika null → auth_enabled = 0 (aman)
        DB::table('whatsapp_gateways')->update([
            'auth_enabled' => DB::raw("CASE WHEN auth_disabled = 'false' OR auth_disabled IS NULL THEN 1 ELSE 0 END")
        ]);

        Schema::table('whatsapp_gateways', function (Blueprint $table) {
            $table->dropColumn('auth_disabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_gateways', function (Blueprint $table) {
            $table->dropColumn('auth_enabled');
            $table->String('auth_disabled')->default('true')->after('basic_auth_password');
        });
    }
};
