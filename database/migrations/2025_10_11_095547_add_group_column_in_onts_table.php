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
        Schema::table('onts', function (Blueprint $table) {
            $table->string('pon_group', 20)->nullable()->after('ont_index')->comment('PON group / port label from OLT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onts', function (Blueprint $table) {
            $table->dropColumn('pon_group');
        });
    }
};
