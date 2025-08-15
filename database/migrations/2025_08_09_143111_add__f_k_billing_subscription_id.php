<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->unsignedBigInteger('billing_subscription_id')->nullable()->after('customer_subscription_id');
        });

        DB::statement("
            UPDATE billings b
            JOIN customer_subscriptions cs ON cs.id = b.customer_subscription_id
            JOIN billing_subscription_periodes bsp
              ON bsp.customer_subscription_id = cs.id
              AND DATE_FORMAT(b.created_at, '%M %Y') = bsp.billing_periode
            SET b.billing_subscription_id = bsp.id
        ");

        Schema::table('billings', function (Blueprint $table) {
            $table->foreign('billing_subscription_id')
                ->references('id')
                ->on('billing_subscription_periodes')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropForeign(['billing_subscription_id']);
            $table->dropColumn('billing_subscription_id');
        });
    }
};
