<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Drop foreign key via raw SQL
        // Ganti 'fk_name' dengan nama foreign key sebenarnya di database kamu
        DB::statement('ALTER TABLE billings DROP FOREIGN KEY billings_customer_subscription_id_foreign');

        // Drop column via raw SQL
        DB::statement('ALTER TABLE billings DROP COLUMN customer_subscription_id');
    }

    public function down()
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_subscription_id')->nullable()->after('id');

            // Foreign key bisa ditambahkan lagi jika diperlukan
            // $table->foreign('customer_subscription_id')->references('id')->on('customer_subscriptions')->onDelete('cascade');
        });
    }
};
