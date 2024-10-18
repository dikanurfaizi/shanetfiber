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
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('number', 16);
                $table->decimal('total_price', 10, 2);
                $table->enum('payment_status', ['1', '2', '3', '4'])->comment('1=menunggu pembayaran, 2=sudah dibayar, 3=kadaluarsa, 4=batal');
                $table->string('snap_token', 36)->nullable();
                $table->foreignId('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->foreignId('billing_id')->references('id')->on('billings')->onDelete('cascade');
                $table->string('transaction_id')->nullable();
                $table->string('payment_type')->nullable();
                $table->string('payment_time')->nullable();
                $table->string('pdf_url')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
