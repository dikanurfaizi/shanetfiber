<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('billings') && Schema::hasColumn('billings', 'billing_periode_id')) {
            Schema::table('billings', function (Blueprint $table) {
                // Drop foreign key by column (Laravel akan cari FK otomatis)
                $table->dropForeign(['billing_periode_id']);
                $table->dropColumn('billing_periode_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('billings') && !Schema::hasColumn('billings', 'billing_periode_id')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->unsignedBigInteger('billing_periode_id')->nullable()->after('customer_subscription_id');
                $table->foreign('billing_periode_id')->references('id')->on('billing_periodes')->onDelete('set null');
            });
        }
    }
};
