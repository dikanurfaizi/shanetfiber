<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscription_changes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            // subscription lama (nullable untuk fleksibilitas)
            $table->foreignId('old_subscription_id')
                ->nullable()
                ->constrained('customer_subscriptions')
                ->nullOnDelete();

            // target package baru
            $table->foreignId('new_package_id')
                ->constrained('packages');

            $table->foreignId('new_package_duration_id')
                ->constrained('package_durations');

            // jenis perubahan
            $table->enum('change_type', ['downgrade', 'upgrade'])
                ->comment('upgrade biasanya immediate, downgrade biasanya scheduled');

            // kapan perubahan dieksekusi
            $table->dateTime('effective_at')
                ->comment('Waktu efektif perubahan (contoh: tanggal 1 00:01)');

            // lifecycle status
            $table->enum('status', [
                'scheduled',
                'applied',
                'cancelled',
                'failed'
            ])->default('scheduled');

            // metadata audit
            $table->dateTime('requested_at');
            $table->foreignId('requested_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // snapshot request (audit & debug)
            $table->json('payload_json')->nullable();

            // error message kalau gagal dieksekusi
            $table->text('error_message')->nullable();

            $table->timestamps();

            /**
             * ======================================================
             * Idempotency guard
             * ======================================================
             * 1 customer tidak boleh punya lebih dari 1 perubahan
             * scheduled dengan tipe yang sama
             */
            $table->unique(
                ['customer_id', 'change_type', 'status'],
                'uniq_customer_change_type_status'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_changes');
    }
};
