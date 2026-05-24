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
        Schema::table('banks', function (Blueprint $blueprint) {
            // Menambahkan kolom 'type' setelah kolom 'bank' dengan tipe data enum
            $blueprint->enum('type', ['bank', 'ewallet'])
                ->default('bank')
                ->after('bank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banks', function (Blueprint $blueprint) {
            // Menghapus kolom 'type' jika migration di-rollback
            $blueprint->dropColumn('type');
        });
    }
};
