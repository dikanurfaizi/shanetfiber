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
        Schema::table('password_history', function (Blueprint $table) {
            // tambahkan kolom updated_at jika belum ada
            if (!Schema::hasColumn('password_history', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_history', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
