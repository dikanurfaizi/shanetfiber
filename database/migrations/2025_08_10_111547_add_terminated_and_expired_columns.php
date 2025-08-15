<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 🔹 customer_subscriptions
        Schema::table('customer_subscriptions', function (Blueprint $table) {
            $table->timestamp('terminated_at')->nullable()->after('status');
        });

        // 🔹 billing_subscription_periodes
        Schema::table('billing_subscription_periodes', function (Blueprint $table) {
            $table->boolean('is_expired')->default(false)->after('is_paid');
            $table->enum('status', ['paid', 'expired', 'unpaid'])->default('unpaid')->after('is_expired');
        });

        // Setelah schema update, jalankan update data existing supaya sinkron:
        DB::table('billing_subscription_periodes')
            ->where('is_paid', 1)
            ->update(['status' => 'paid']);

        DB::table('billing_subscription_periodes')
            ->where('is_paid', 0)
            ->update(['status' => 'unpaid']);
    }

    public function down(): void
    {
        Schema::table('customer_subscriptions', function (Blueprint $table) {
            $table->dropColumn('terminated_at');
        });

        Schema::table('billing_subscription_periodes', function (Blueprint $table) {
            $table->dropColumn(['is_expired', 'status']);
        });
    }
};
