<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ Hilangkan AUTO_INCREMENT dari kolom id jika masih ada
        if (Schema::hasColumn('ont_archives', 'id')) {
            DB::statement('ALTER TABLE ont_archives MODIFY COLUMN id BIGINT UNSIGNED NOT NULL');
        }

        // 2️⃣ Hapus primary key lama
        try {
            DB::statement('ALTER TABLE ont_archives DROP PRIMARY KEY');
        } catch (\Throwable $e) {
            // abaikan kalau PK sudah tidak ada
        }

        // 3️⃣ Hapus kolom id jika masih ada
        Schema::table('ont_archives', function (Blueprint $table) {
            if (Schema::hasColumn('ont_archives', 'id')) {
                $table->dropColumn('id');
            }
        });

        // 4️⃣ Tambahkan composite primary key baru
        Schema::table('ont_archives', function (Blueprint $table) {
            $table->primary(['source_olt_id', 'ont_index', 'archived_at']);
        });

        // 5️⃣ Tambahkan index hanya jika belum ada
        $existingIndexes = DB::table('information_schema.statistics')
            ->select('INDEX_NAME')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', 'ont_archives')
            ->pluck('INDEX_NAME')
            ->toArray();

        Schema::table('ont_archives', function (Blueprint $table) use ($existingIndexes) {
            if (!in_array('ont_archives_status_index', $existingIndexes)) {
                $table->index('status');
            }
            if (!in_array('ont_archives_pon_group_index', $existingIndexes)) {
                $table->index('pon_group');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ont_archives', function (Blueprint $table) {
            // Drop composite PK
            DB::statement('ALTER TABLE ont_archives DROP PRIMARY KEY');

            // Tambahkan kembali kolom id auto increment
            $table->bigIncrements('id')->first();
        });
    }
};
