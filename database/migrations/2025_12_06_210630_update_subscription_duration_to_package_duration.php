<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_subscriptions', function (Blueprint $table) {
            // 1. Drop foreign key lama
            $table->dropForeign('customer_subscriptions_subscription_duration_id_foreign');

            // 2. Rename kolom
            $table->renameColumn('subscription_duration_id', 'package_duration_id');
        });

        Schema::table('customer_subscriptions', function (Blueprint $table) {
            // 3. Tambahkan foreign key baru
            $table
                ->foreign('package_duration_id')
                ->references('id')
                ->on('package_durations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_subscriptions', function (Blueprint $table) {
            // Balikkan proses
            $table->dropForeign(['package_duration_id']);
            $table->renameColumn('package_duration_id', 'subscription_duration_id');
        });

        Schema::table('customer_subscriptions', function (Blueprint $table) {
            $table
                ->foreign('subscription_duration_id')
                ->references('id')
                ->on('subscription_durations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};
