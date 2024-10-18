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
        if (!Schema::hasTable('billing_periodes')) {
            Schema::create('billing_periodes', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('billings')) {

            Schema::create('billings', function (Blueprint $table) {
                $table->id();
                $table->string('slug');
                $table->integer('month');
                $table->integer('year');
                $table->foreignId('billing_periode_id')->references('id')->on('billing_periodes')->onDelete('cascade');
                $table->string('billing_number');
                $table->foreignId('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->string('customer_name');
                $table->string('paket_name');
                $table->integer('paket_price');
                $table->string('created_name');
                $table->enum('status', ['BL', 'LS']);
                $table->string('teller_name')->nullable(true);
                $table->string('payment_time')->nullable(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings', 'billing_periodes');
    }
};
