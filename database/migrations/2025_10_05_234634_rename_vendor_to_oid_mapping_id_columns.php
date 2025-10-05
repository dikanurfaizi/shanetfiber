<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('olts', function (Blueprint $table) {
            $table->unsignedBigInteger('oid_mapping_id')->default(1)->after('vendor');
        });

        // Update semua value menjadi 1
        DB::table('olts')->update(['oid_mapping_id' => 1]);

        // Tambahkan foreign key
        Schema::table('olts', function (Blueprint $table) {
            $table->foreign('oid_mapping_id')
                ->references('id')
                ->on('oid_mappings')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        // Opsional: hapus kolom vendor lama
        Schema::table('olts', function (Blueprint $table) {
            $table->dropColumn('vendor');
        });
    }

    public function down(): void
    {
        Schema::table('olts', function (Blueprint $table) {
            $table->string('vendor')->after('oid_mapping_id');
            $table->dropForeign(['oid_mapping_id']);
            $table->dropColumn('oid_mapping_id');
        });
    }
};
