<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_subscriptions', function (Blueprint $table) {
            // Ditambahkan setelah discount_value_final
            $table->boolean('is_prorated')
                ->default(false)
                ->after('discount_value_final')
                ->comment('Penanda apakah siklus ini memakai harga prorate');
        });
    }

    public function down(): void
    {
        Schema::table('customer_subscriptions', function (Blueprint $table) {
            $table->dropColumn('is_prorated');
        });
    }
};
