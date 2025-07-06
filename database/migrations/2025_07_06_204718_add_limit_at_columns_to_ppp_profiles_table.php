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
        Schema::table('ppp_profiles', function (Blueprint $table) {
            $table->string('limit_at_upload')->nullable()->after('burst_time_download');
            $table->string('limit_at_download')->nullable()->after('limit_at_upload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppp_profiles', function (Blueprint $table) {
            $table->dropColumn(['limit_at_upload', 'limit_at_download']);
        });
    }
};
