<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('banks')) {
            // 1. Pastikan kolom user_id ada, jika belum tambahkan dulu sebagai nullable
            Schema::table('banks', function (Blueprint $table) {
                if (!Schema::hasColumn('banks', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable();
                }
            });

            // 2. Ubah user_id menjadi NOT NULL menggunakan SQL statement langsung
            DB::statement('ALTER TABLE banks MODIFY user_id BIGINT UNSIGNED NOT NULL');

            // 3. Tambahkan Foreign Key constraint jika belum ada
            Schema::table('banks', function (Blueprint $table) {
                // Opsional: Cek untuk menghindari duplikasi foreign key jika dijalankan berulang
                $table->foreign('user_id', 'banks_user_id_foreign')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('banks')) {
            Schema::table('banks', function (Blueprint $table) {
                // Hapus foreign key constraint dengan nama spesifik
                try {
                    $table->dropForeign('banks_user_id_foreign');
                } catch (\Exception $e) {
                    // Abaikan jika constraint sudah tidak ada
                }
            });

            // Kembalikan kolom user_id menjadi nullable via SQL statement
            if (Schema::hasColumn('banks', 'user_id')) {
                DB::statement('ALTER TABLE banks MODIFY user_id BIGINT UNSIGNED NULL');
            }
        }
    }
};
