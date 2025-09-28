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
        Schema::create('oid_mappings', function (Blueprint $table) {
            $table->string('vendor', 50)->primary();
            $table->string('oid_name', 255)->nullable();
            $table->string('oid_tx', 255)->nullable();
            $table->string('oid_rx', 255)->nullable();
            $table->string('oid_status', 255)->nullable();
            $table->string('oid_mac', 255)->nullable();
            $table->string('oid_temp', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oid_mappings');
    }
};
