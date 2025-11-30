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
        // billings
        Schema::table('billings', function (Blueprint $table) {
            $table->index('billing_subscription_id', 'idx_billing_subscription_id');
        });

        // billing_subscription_periodes
        Schema::table('billing_subscription_periodes', function (Blueprint $table) {
            $table->index('billing_periode_id', 'idx_bsp_billing_periode_id');
        });

        // billing_periodes
        Schema::table('billing_periodes', function (Blueprint $table) {
            $table->index('name', 'idx_bp_name');
        });

        // customers
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['user_id', 'mikrotik_id'], 'idx_customers_user_mikrotik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // rollback indexes

        Schema::table('billings', function (Blueprint $table) {
            $table->dropIndex('idx_billing_subscription_id');
        });

        Schema::table('billing_subscription_periodes', function (Blueprint $table) {
            $table->dropIndex('idx_bsp_billing_periode_id');
        });

        Schema::table('billing_periodes', function (Blueprint $table) {
            $table->dropIndex('idx_bp_name');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_user_mikrotik');
        });
    }
};
