<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('whatsapp_cloud_template_buttons')) {
            // 1. Tambahkan kolom baru jika belum ada
            Schema::table('whatsapp_cloud_template_buttons', function (Blueprint $table) {
                if (!Schema::hasColumn('whatsapp_cloud_template_buttons', 'phone_number')) {
                    $table->string('phone_number', 50)->nullable();
                }
                if (!Schema::hasColumn('whatsapp_cloud_template_buttons', 'payload')) {
                    $table->string('payload', 255)->nullable();
                }
                if (!Schema::hasColumn('whatsapp_cloud_template_buttons', 'code')) {
                    $table->string('code', 255)->nullable();
                }
            });

            // 2. Ubah tipe kolom 'type' menggunakan SQL statement mentah (Aman dari error change())
            if (Schema::hasColumn('whatsapp_cloud_template_buttons', 'type')) {
                DB::statement('ALTER TABLE whatsapp_cloud_template_buttons MODIFY type VARCHAR(30) NOT NULL');
            }

            // 3. Tambahkan index jika belum ada atau aman untuk dibuat
            try {
                DB::statement('ALTER TABLE whatsapp_cloud_template_buttons ADD INDEX whatsapp_cloud_template_buttons_type_position_index (type, position)');
            } catch (\Throwable $e) {
                // Abaikan jika index sudah ada
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('whatsapp_cloud_template_buttons')) {
            // Hapus index jika ada
            try {
                DB::statement('ALTER TABLE whatsapp_cloud_template_buttons DROP INDEX whatsapp_cloud_template_buttons_type_position_index');
            } catch (\Throwable $e) {
            }

            // Hapus kolom baru
            Schema::table('whatsapp_cloud_template_buttons', function (Blueprint $table) {
                $columnsToDrop = ['phone_number', 'payload', 'code'];
                $existingColumns = array_filter($columnsToDrop, function ($col) {
                    return Schema::hasColumn('whatsapp_cloud_template_buttons', $col);
                });

                if (!empty($existingColumns)) {
                    $table->dropColumn($existingColumns);
                }
            });
        }
    }
};
