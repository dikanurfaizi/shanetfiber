<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompositeIndexesToCustomerSubscriptionsAndBillingSubscriptionPeriodes extends Migration
{
    public function up()
    {
        Schema::table('customer_subscriptions', function (Blueprint $table) {
            $table->index(['customer_id', 'paket_id'], 'idx_customer_paket');
        });

        Schema::table('billing_subscription_periodes', function (Blueprint $table) {
            $table->index(['billing_periode_id', 'customer_subscription_id'], 'idx_periode_customer_subscription');
        });
    }

    public function down()
    {
        Schema::table('customer_subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_customer_paket');
        });

        Schema::table('billing_subscription_periodes', function (Blueprint $table) {
            $table->dropIndex('idx_periode_customer_subscription');
        });
    }
}
