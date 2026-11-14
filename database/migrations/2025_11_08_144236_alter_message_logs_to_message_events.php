<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Rename table bila perlu
        if (Schema::hasTable('message_logs') && !Schema::hasTable('message_events')) {
            Schema::rename('message_logs', 'message_events');
        }

        // 2) Lepas FK lama kalau masih menempel (nama yang kamu temukan)
        foreach (['message_logs_gateway_id_foreign', 'message_logs_customer_id_foreign'] as $fk) {
            try {
                DB::statement("ALTER TABLE message_events DROP FOREIGN KEY {$fk}");
            } catch (\Throwable $e) {
            }
        }

        // 3) Tambah/ubah kolom menggunakan Schema builder untuk penambahan baru
        Schema::table('message_events', function (Blueprint $table) {
            if (!Schema::hasColumn('message_events', 'gateway_id')) {
                $table->unsignedBigInteger('gateway_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('message_events', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('gateway_id');
            }

            if (!Schema::hasColumn('message_events', 'channel')) {
                $table->string('channel', 50)->after('customer_id');
            }

            if (!Schema::hasColumn('message_events', 'status')) {
                $table->enum('status', ['success', 'failed', 'pending'])->default('success')->after('message_type');
            }

            if (!Schema::hasColumn('message_events', 'response_code')) {
                $table->string('response_code', 50)->nullable()->after('status');
            }

            if (!Schema::hasColumn('message_events', 'response_message')) {
                $table->text('response_message')->nullable()->after('response_code');
            }

            if (!Schema::hasColumn('message_events', 'message_id')) {
                $table->string('message_id', 50)->nullable()->after('response_message');
            }

            if (!Schema::hasColumn('message_events', 'trace_id')) {
                $table->string('trace_id', 100)->after('message_id');
            }
        });

        // Modifikasi posisi/tipe gateway_id menggunakan DB::statement (Aman dari error change())
        if (Schema::hasColumn('message_events', 'gateway_id')) {
            try {
                DB::statement("ALTER TABLE message_events MODIFY COLUMN gateway_id BIGINT UNSIGNED NULL AFTER id");
            } catch (\Throwable $e) {
            }
        }

        // 4) Buat kolom meta (JSON) jika belum ada
        if (!Schema::hasColumn('message_events', 'meta')) {
            try {
                DB::statement('ALTER TABLE message_events ADD COLUMN meta JSON NULL AFTER message_type');
            } catch (\Throwable $e) {
                // fallback bila engine/versi MySQL tidak support JSON: gunakan LONGTEXT
                DB::statement('ALTER TABLE message_events ADD COLUMN meta LONGTEXT NULL AFTER message_type');
            }
        }

        // 5) Migrasi data dari content -> meta, bila content masih ada
        if (Schema::hasColumn('message_events', 'content') && Schema::hasColumn('message_events', 'meta')) {
            try {
                // Jika content JSON valid, cast; jika bukan, bungkus: {"text": content}
                DB::statement("
                    UPDATE message_events
                    SET meta = IF(
                        JSON_VALID(content),
                        JSON_EXTRACT(content, '$'),
                        JSON_OBJECT('text', content)
                    )
                    WHERE meta IS NULL
                ");
                // Hapus kolom content setelah migrasi
                DB::statement("ALTER TABLE message_events DROP COLUMN content");
            } catch (\Throwable $e) {
                // Jika DB tidak support JSON_* (versi lawas), simpan sebagai raw text
                try {
                    DB::statement("
                        UPDATE message_events
                        SET meta = content
                        WHERE meta IS NULL
                    ");
                    DB::statement("ALTER TABLE message_events DROP COLUMN content");
                } catch (\Throwable $e2) {
                    // Biarkan content tetap ada jika benar2 tidak bisa di-drop
                }
            }
        }

        // 6) (Opsional) Rapikan urutan kolom status & response_message setelah meta
        try {
            DB::statement("ALTER TABLE message_events MODIFY COLUMN status ENUM('success','failed','pending') NOT NULL DEFAULT 'success' AFTER meta");
            DB::statement("ALTER TABLE message_events MODIFY COLUMN response_message TEXT NULL AFTER response_code");
        } catch (\Throwable $e) {
        }

        // 7) (Opsional) Index untuk query dashboard
        try {
            DB::statement("CREATE INDEX idx_message_events_channel_status ON message_events (channel, status)");
        } catch (\Throwable $e) {
        }
        try {
            DB::statement("CREATE INDEX idx_message_events_customer ON message_events (customer_id)");
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        // Kembalikan content bila sebelumnya ada migrasi
        if (Schema::hasTable('message_events')) {
            if (!Schema::hasColumn('message_events', 'content') && Schema::hasColumn('message_events', 'meta')) {
                // Buat content TEXT dan isi balik dari meta->text jika ada
                try {
                    DB::statement("ALTER TABLE message_events ADD COLUMN content LONGTEXT NULL AFTER message_type");
                } catch (\Throwable $e) {
                }

                try {
                    DB::statement("
                        UPDATE message_events
                        SET content = CASE
                            WHEN JSON_VALID(meta) AND JSON_EXTRACT(meta, '$.text') IS NOT NULL
                                THEN JSON_UNQUOTE(JSON_EXTRACT(meta, '$.text'))
                            ELSE meta
                        END
                    ");
                } catch (\Throwable $e) {
                    try {
                        DB::statement("UPDATE message_events SET content = meta");
                    } catch (\Throwable $e2) {
                    }
                }
            }

            // Hapus kolom baru
            Schema::table('message_events', function (Blueprint $table) {
                if (Schema::hasColumn('message_events', 'response_code')) $table->dropColumn('response_code');
                if (Schema::hasColumn('message_events', 'response_message')) $table->dropColumn('response_message');
                if (Schema::hasColumn('message_events', 'message_id')) $table->dropColumn('message_id');
                if (Schema::hasColumn('message_events', 'status')) $table->dropColumn('status');
                if (Schema::hasColumn('message_events', 'meta')) $table->dropColumn('meta');
                if (Schema::hasColumn('message_events', 'channel')) $table->dropColumn('channel');
                if (Schema::hasColumn('message_events', 'trace_id')) $table->dropColumn('trace_id');
            });

            // Rename balik tabel
            Schema::rename('message_events', 'message_logs');
        }
    }
};
