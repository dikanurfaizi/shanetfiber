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
        Schema::table('whatsapp_cloud_template_buttons', function (Blueprint $table) {
            $table->json('example')->nullable()->after('raw_button');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_cloud_template_buttons', function (Blueprint $table) {
            $table->dropColumn('example');
        });
    }
};
