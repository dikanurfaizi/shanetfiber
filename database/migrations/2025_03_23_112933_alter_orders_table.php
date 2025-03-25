<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Pastikan payment_channel_code ada sebelum rename
            if (Schema::hasColumn('orders', 'payment_channel_code')) {
                $table->renameColumn('payment_channel_code', 'payment_method');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_fee', 15, 2)->nullable()->after('total_price');
            $table->decimal('total_amount', 15, 2)->nullable()->after('total_fee');
            $table->string('va_number')->nullable()->after('updated_at');
            $table->string('expired_time')->nullable()->after('va_number');
            $table->integer('quantity')->nullable()->after('package');
            $table->string('payment_name')->nullable()->after('payment_method');
            $table->string('checkout_url')->nullable();
            $table->string('reference')->nullable()->after('number');
            $table->string('payment_selection_type')->nullable()->after('payment_time');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['total_fee', 'va_number', 'expired_time', 'quantity', 'payment_name', 'checkout_url', 'reference', 'payment_selection_type']);
            $table->renameColumn('payment_method', 'payment_channel_code');
        });
    }
};
