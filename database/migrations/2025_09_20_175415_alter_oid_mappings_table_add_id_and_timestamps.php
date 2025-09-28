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
        Schema::table('oid_mappings', function (Blueprint $table) {
            // Hapus primary key lama di vendor
            DB::statement('ALTER TABLE oid_mappings DROP PRIMARY KEY');

            // Tambahkan kolom id sebagai PK auto increment
            $table->id()->first();

            // Tambahkan timestamps dan soft deletes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oid_mappings', function (Blueprint $table) {
            // Hapus kolom baru
            $table->dropColumn(['id', 'created_at', 'updated_at', 'deleted_at']);

            // Vendor jadi PK lagi
            DB::statement('ALTER TABLE oid_mappings ADD PRIMARY KEY (vendor)');
        });
    }
};
