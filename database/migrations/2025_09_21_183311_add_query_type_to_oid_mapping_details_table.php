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
        Schema::table('oid_mapping_details', function (Blueprint $table) {
            $table->tinyInteger('query_type')
                ->default(2)
                ->comment('1 = OLT, 2 = ONU')
                ->after('oid_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oid_mapping_details', function (Blueprint $table) {
            $table->dropColumn('query_type');
        });
    }
};
