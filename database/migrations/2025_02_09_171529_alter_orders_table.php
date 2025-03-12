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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('signature')->nullable()->after('payment_status');
            $table->string('payment_channel_code')->nullable()->after('payment_type');
            $table->string('payment_gateway')->nullable()->after('payment_time');
            $table->string('icon_url')->nullable()->after('payment_gateway');
            $table->string('package')->nullable()->after('number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['signature', 'payment_channel_code', 'payment_gateway', 'icon_url', 'package']);
        });
    }
};
