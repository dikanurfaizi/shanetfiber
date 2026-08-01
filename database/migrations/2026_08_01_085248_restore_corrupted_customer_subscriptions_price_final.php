<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            UPDATE customer_subscriptions cs
            JOIN packages p ON cs.package_id = p.id
            LEFT JOIN package_durations pd ON cs.package_duration_id = pd.id
            JOIN (
                SELECT DISTINCT bsp.customer_subscription_id
                FROM billing_items bi 
                JOIN billings b ON bi.billing_id = b.id
                JOIN billing_subscription_periodes bsp ON b.billing_subscription_id = bsp.id
                JOIN customer_subscriptions cs_sub ON bsp.customer_subscription_id = cs_sub.id
                JOIN packages p_sub ON cs_sub.package_id = p_sub.id
                WHERE bi.item_type = 'discount' 
                  AND bi.source_type = 'membership'
                  AND cs_sub.price_final = (p_sub.price - ABS(bi.subtotal))
            ) affected ON cs.id = affected.customer_subscription_id
            SET 
                cs.price_final = CASE 
                    WHEN pd.discount_type = 'percent' AND pd.discount_value > 0 
                        THEN GREATEST(0, p.price - (p.price * (pd.discount_value / 100)))
                    WHEN pd.discount_type = 'amount' AND pd.discount_value > 0 
                        THEN GREATEST(0, p.price - pd.discount_value)
                    ELSE p.price
                END,
                cs.discount_type_final = COALESCE(pd.discount_type, 'none'),
                cs.discount_value_final = COALESCE(pd.discount_value, 0.00)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Down migration dikosongkan karena perbaikan data terpolusi tidak perlu di-rollback
    }
};
