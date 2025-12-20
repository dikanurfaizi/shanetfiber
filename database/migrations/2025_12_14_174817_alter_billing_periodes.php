<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_periodes', function (Blueprint $table) {
            $table->date('start_date')
                ->nullable()
                ->after('month');

            $table->date('end_date')
                ->nullable()
                ->after('start_date');

            $table->enum('status', [
                'draft',
                'active',
                'closed',
                'cancelled',
            ])
                ->default('draft')
                ->after('end_date')
                ->index();
        });

        /**
         * ===========================
         * DATA MIGRATION (ONE-TIME)
         * ===========================
         */

        // 1️⃣ Hitung start_date & end_date dari month (January) + year (2025)
        DB::statement("
            UPDATE billing_periodes
            SET
                start_date = STR_TO_DATE(
                    CONCAT(
                        year, '-',
                        LPAD(
                            CASE month
                                WHEN 'January'   THEN 1
                                WHEN 'February'  THEN 2
                                WHEN 'March'     THEN 3
                                WHEN 'April'     THEN 4
                                WHEN 'May'       THEN 5
                                WHEN 'June'      THEN 6
                                WHEN 'July'      THEN 7
                                WHEN 'August'    THEN 8
                                WHEN 'September' THEN 9
                                WHEN 'October'   THEN 10
                                WHEN 'November'  THEN 11
                                WHEN 'December'  THEN 12
                            END,
                        2, '0'
                        ),
                        '-01'
                    ),
                    '%Y-%m-%d'
                ),
                end_date = LAST_DAY(
                    STR_TO_DATE(
                        CONCAT(
                            year, '-',
                            LPAD(
                                CASE month
                                    WHEN 'January'   THEN 1
                                    WHEN 'February'  THEN 2
                                    WHEN 'March'     THEN 3
                                    WHEN 'April'     THEN 4
                                    WHEN 'May'       THEN 5
                                    WHEN 'June'      THEN 6
                                    WHEN 'July'      THEN 7
                                    WHEN 'August'    THEN 8
                                    WHEN 'September' THEN 9
                                    WHEN 'October'   THEN 10
                                    WHEN 'November'  THEN 11
                                    WHEN 'December'  THEN 12
                                END,
                            2, '0'
                            ),
                            '-01'
                        ),
                        '%Y-%m-%d'
                    )
                )
        ");

        // 2️⃣ Tutup SEMUA billing_periodes
        DB::statement("
            UPDATE billing_periodes
            SET status = 'closed'
        ");

        // 3️⃣ Aktifkan HANYA yang TERBARU (by created_at)
        DB::statement("
            UPDATE billing_periodes
            SET status = 'active'
            WHERE id = (
                SELECT id FROM (
                    SELECT id
                    FROM billing_periodes
                    ORDER BY created_at DESC
                    LIMIT 1
                ) latest
            )
        ");
    }

    public function down(): void
    {
        Schema::table('billing_periodes', function (Blueprint $table) {
            $table->dropIndex(['status']);

            $table->dropColumn([
                'status',
                'end_date',
                'start_date',
            ]);
        });
    }
};
