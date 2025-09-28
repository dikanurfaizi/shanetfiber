<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oid_mapping_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mapping_id')
                ->constrained('oid_mappings')
                ->onDelete('cascade'); // kalau vendor dihapus, detail ikut terhapus
            $table->string('oid_key', 100);      // contoh: sysdescr, txpower, rxpower
            $table->string('oid_value', 255);    // OID string
            $table->string('description', 255)->nullable(); // catatan opsional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oid_mapping_details');
    }
};
