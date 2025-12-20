<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // --- CUSTOMERS TABLE ---
        DB::transaction(function () {

            if (!Schema::hasColumn('customers', 'package_id')) {
                return;
            }

            /**
             * 1. Temp table untuk menentukan 1 ACTIVE subscription per customer
             */
            DB::statement("
                CREATE TEMPORARY TABLE tmp_active_subs (
                    customer_id BIGINT UNSIGNED PRIMARY KEY,
                    active_sub_id BIGINT UNSIGNED NULL
                ) ENGINE=MEMORY
            ");

            /**
             * 2. Cari subscription yang MATCH package_id customer
             */
            DB::statement("
                INSERT INTO tmp_active_subs (customer_id, active_sub_id)
                SELECT
                    c.id,
                    MAX(cs.id)
                FROM customers c
                LEFT JOIN customer_subscriptions cs
                    ON cs.customer_id = c.id
                   AND cs.package_id = c.package_id
                   AND cs.deleted_at IS NULL
                WHERE c.package_id IS NOT NULL
                GROUP BY c.id
            ");

            /**
             * 3. AUTO-CREATE subscription jika BELUM ADA match
             */
            DB::statement("
                INSERT INTO customer_subscriptions (
                    customer_id,
                    package_id,
                    package_duration_id,
                    status,
                    start_date,
                    end_date,
                    price_final,
                    discount_type_final,
                    discount_value_final,
                    created_at,
                    updated_at
                )
                SELECT
                    c.id,
                    c.package_id,
                    (
                        SELECT MIN(pd.id)
                        FROM package_durations pd
                        WHERE pd.package_id = c.package_id
                    ),
                    'active',
                    COALESCE(c.activation_date, CURRENT_DATE),
                    CASE
                        WHEN c.deleted_at IS NOT NULL
                            THEN DATE_FORMAT(CURRENT_DATE, '%Y-%m-15')
                        ELSE DATE_ADD(
                            COALESCE(c.activation_date, CURRENT_DATE),
                            INTERVAL 1 MONTH
                        )
                    END,
                    p.price,
                    'none',
                    0,
                    NOW(),
                    NOW()
                FROM customers c
                INNER JOIN tmp_active_subs t
                    ON t.customer_id = c.id
                INNER JOIN packages p
                    ON p.id = c.package_id
                WHERE c.package_id IS NOT NULL
                  AND t.active_sub_id IS NULL
            ");

            /**
             * 4. Refresh tmp_active_subs setelah auto-create
             */
            DB::statement("
                UPDATE tmp_active_subs t
                SET active_sub_id = (
                    SELECT MAX(cs.id)
                    FROM customer_subscriptions cs
                    WHERE cs.customer_id = t.customer_id
                      AND cs.status = 'active'
                      AND cs.deleted_at IS NULL
                )
            ");

            /**
             * 5. Terminate semua subscription lain
             */
            DB::statement("
                UPDATE customer_subscriptions cs
                INNER JOIN tmp_active_subs t
                    ON t.customer_id = cs.customer_id
                SET
                    cs.status = 'terminated',
                    cs.terminated_at = COALESCE(cs.terminated_at, NOW())
                WHERE cs.deleted_at IS NULL
                  AND cs.id <> t.active_sub_id
                  AND cs.status <> 'terminated'
            ");

            /**
             * 6. Pastikan subscription terpilih ACTIVE
             */
            DB::statement("
                UPDATE customer_subscriptions cs
                INNER JOIN tmp_active_subs t
                    ON cs.id = t.active_sub_id
                SET
                    cs.status = 'active',
                    cs.terminated_at = NULL
                WHERE cs.deleted_at IS NULL
            ");

            DB::statement("DROP TEMPORARY TABLE IF EXISTS tmp_active_subs");

            /**
             * 7. Drop kolom legacy di customers
             */
            Schema::table('customers', function (Blueprint $table) {

                if (Schema::hasColumn('customers', 'package_id')) {
                    $table->dropForeign(['package_id']);
                    $table->dropColumn('package_id');
                }

                if (Schema::hasColumn('customers', 'discount')) {
                    $table->dropColumn('discount');
                }
            });
        });

        // --- BILLINGS TABLE ---
        Schema::table('billings', function (Blueprint $table) {
            if (Schema::hasColumn('billings', 'paid_by_user_id')) {
                $table->dropColumn('paid_by_user_id');
            }

            if (Schema::hasColumn('billings', 'payment_time')) {
                $table->dropColumn('payment_time');
            }

            // rename pay_later → paylater_until
            if (Schema::hasColumn('billings', 'pay_later')) {
                $table->renameColumn('pay_later', 'paylater_until');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'package_id')) {
                $table->unsignedBigInteger('package_id')->nullable();
            }

            if (!Schema::hasColumn('customers', 'discount')) {
                $table->decimal('discount', 12, 2)->nullable();
            }
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->unsignedBigInteger('paid_by_user_id')->nullable();
            $table->timestamp('payment_time')->nullable();

            // rename back
            if (Schema::hasColumn('billings', 'paylater_until')) {
                $table->renameColumn('paylater_until', 'pay_later');
            }
        });
    }
};
