<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void
    {
        /**
         * 1. Tambah kolom status
         */
        Schema::table('billing_subscription_periodes', function (Blueprint $table) {
            $table->enum('status', ['active', 'expired'])
                ->default('expired')
                ->after('billing_periode_id');
        });

        /**
         * 2. Baseline: semua expired
         */
        DB::statement("
            UPDATE billing_subscription_periodes
            SET status = 'expired'
        ");

        /**
         * 3. Set ACTIVE hanya untuk:
         *    - subscription masih ACTIVE
         *    - BSP tidak soft-deleted
         *    - periode TERBARU per subscription
         */
        DB::statement("
            UPDATE billing_subscription_periodes bsp
            INNER JOIN (
                SELECT
                    bsp.customer_subscription_id,
                    MAX(bsp.billing_periode_id) AS max_pid
                FROM billing_subscription_periodes bsp
                INNER JOIN customer_subscriptions cs
                    ON cs.id = bsp.customer_subscription_id
                WHERE cs.status = 'active'
                  AND bsp.deleted_at IS NULL
                GROUP BY bsp.customer_subscription_id
            ) x
              ON x.customer_subscription_id = bsp.customer_subscription_id
             AND x.max_pid = bsp.billing_periode_id
            SET bsp.status = 'active'
        ");

        /**
         * 4. Drop legacy column is_expired
         */
        Schema::table('billing_subscription_periodes', function (Blueprint $table) {
            if (Schema::hasColumn('billing_subscription_periodes', 'is_expired')) {
                $table->dropColumn('is_expired');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_subscription_periodes', function (Blueprint $table) {
            if (Schema::hasColumn('billing_subscription_periodes', 'status')) {
                $table->dropColumn('status');
            }

            if (!Schema::hasColumn('billing_subscription_periodes', 'is_expired')) {
                $table->boolean('is_expired')
                    ->default(0)
                    ->after('billing_periode_id');
            }
        });
    }
};
