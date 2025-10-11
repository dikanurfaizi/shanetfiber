<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ Hapus atribut AUTO_INCREMENT dari kolom id
        DB::statement('ALTER TABLE onts MODIFY COLUMN id BIGINT UNSIGNED NOT NULL');

        // 2️⃣ Hapus primary key lama
        DB::statement('ALTER TABLE onts DROP PRIMARY KEY');

        // 3️⃣ Hapus kolom id sepenuhnya
        Schema::table('onts', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        // 4️⃣ Tambahkan primary key gabungan
        Schema::table('onts', function (Blueprint $table) {
            $table->primary(['olt_id', 'ont_index']);
        });

        // 5️⃣ Tambahkan index tambahan opsional
        Schema::table('onts', function (Blueprint $table) {
            $table->index('status');
            $table->index('pon_group');
        });
    }

    public function down(): void
    {
        // Balikkan perubahan: tambahkan kolom id auto increment lagi
        Schema::table('onts', function (Blueprint $table) {
            $table->dropPrimary(['olt_id', 'ont_index']);
            $table->bigIncrements('id')->first();
            $table->dropIndex(['status']);
            $table->dropIndex(['pon_group']);
        });
    }
};
