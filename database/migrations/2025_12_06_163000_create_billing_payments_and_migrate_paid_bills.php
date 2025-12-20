<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Tambahkan kolom billing_payment_id di receipts
        |--------------------------------------------------------------------------
        */
        Schema::table('receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('receipts', 'billing_payment_id')) {
                $table->unsignedBigInteger('billing_payment_id')->nullable()->after('id');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Create billing_payments table
        |--------------------------------------------------------------------------
        */
        Schema::create('billing_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billing_id');
            $table->decimal('amount_paid', 12, 2);
            $table->string('payment_method')->nullable();
            $table->string('reference_id')->nullable();
            $table->unsignedBigInteger('paid_by_user_id')->nullable();
            $table->timestamp('payment_time')->nullable();
            $table->timestamps();

            $table->foreign('billing_id')
                ->references('id')->on('billings')
                ->restrictOnDelete(); // Payment tidak boleh hilang bila billing dihapus

            $table->foreign('paid_by_user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | 3. Generate billing_payments untuk billing LS lama
        |--------------------------------------------------------------------------
        */
        $paidBills = DB::table('billings')->where('status_new', 'LS')->get();

        foreach ($paidBills as $b) {

            // receipt optional (boleh null)
            $receipt = $b->receipt_id
                ? DB::table('receipts')->where('id', $b->receipt_id)->first()
                : null;

            // orders aman karena kamu bilang selalu ada, tapi tetap defensive
            $order = DB::table('orders')->where('billing_id', $b->id)->first();

            $paymentId = DB::table('billing_payments')->insertGetId([
                'billing_id'      => $b->id,
                'amount_paid'     => $b->total_amount ?? 0,
                'payment_method'  => $receipt?->payment_methode ?? null,
                'reference_id'    => $order?->reference ?? null,
                'paid_by_user_id' => $b->paid_by_user_id,
                'payment_time'    => $b->payment_time,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Update receipt → billing_payment_id (optional)
            if ($receipt) {
                DB::table('receipts')
                    ->where('id', $receipt->id)
                    ->update(['billing_payment_id' => $paymentId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            if (Schema::hasColumn('receipts', 'billing_payment_id')) {
                $table->dropColumn('billing_payment_id');
            }
        });

        Schema::dropIfExists('billing_payments');
    }
};
