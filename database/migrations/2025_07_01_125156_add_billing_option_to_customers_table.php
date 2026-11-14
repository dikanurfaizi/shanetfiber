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
        if (Schema::hasTable('customers')) {
            if (!Schema::hasColumn('customers', 'billing_option')) {
                // Jika kolom belum ada, tambahkan langsung sebagai nullable
                Schema::table('customers', function (Blueprint $table) {
                    $table->unsignedBigInteger('billing_option')->nullable()->after('member_id');
                });
            } else {
                // Jika kolom sudah ada, ubah menjadi nullable menggunakan SQL statement yang aman
                DB::statement('ALTER TABLE customers MODIFY billing_option BIGINT UNSIGNED NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'billing_option')) {
            // Kembalikan menjadi non-nullable via SQL statement
            DB::statement('ALTER TABLE customers MODIFY billing_option BIGINT UNSIGNED NOT NULL');
        }
    }
};
