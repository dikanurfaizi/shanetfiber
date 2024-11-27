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
        // Tambahkan softDeletes dan address_list_isolir di tabel auto_isolirs jika belum ada
        if (Schema::hasTable('auto_isolirs')) {
            Schema::table('auto_isolirs', function (Blueprint $table) {
                if (!Schema::hasColumn('auto_isolirs', 'address_list_isolir')) {
                    $table->string('address_list_isolir')->nullable();
                }

                if (!Schema::hasColumn('auto_isolirs', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert perubahan di tabel auto_isolirs
        if (Schema::hasTable('auto_isolirs')) {
            Schema::table('auto_isolirs', function (Blueprint $table) {
                // Hapus kolom baru jika ada
                if (Schema::hasColumn('auto_isolirs', 'address_list_isolir')) {
                    $table->dropColumn('address_list_isolir');
                }
                if (Schema::hasColumn('auto_isolirs', 'deleted_at')) {
                    $table->dropColumn('deleted_at');
                }
            });
        }
    }
};
