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
        // Tambahkan softDeletes jika kolomnya belum ada
        if (!Schema::hasColumn('billing_cycles', 'deleted_at')) {
            Schema::table('billing_cycles', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('paket_categories', 'deleted_at')) {
            Schema::table('paket_categories', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus kolom deleted_at jika ada
        Schema::table('billing_cycles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('paket_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
