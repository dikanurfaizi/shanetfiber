<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('billing_subscription_periodes', function (Blueprint $table) {

            // Drop columns only if they exist (safe for production)
            if (Schema::hasColumn('billing_subscription_periodes', 'billing_periode')) {
                $table->dropColumn('billing_periode');
            }

            if (Schema::hasColumn('billing_subscription_periodes', 'is_paid')) {
                $table->dropColumn('is_paid');
            }

            if (Schema::hasColumn('billing_subscription_periodes', 'paid_at')) {
                $table->dropColumn('paid_at');
            }

            if (Schema::hasColumn('billing_subscription_periodes', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_subscription_periodes', function (Blueprint $table) {

            // Recreate columns with their original definitions (best effort)

            if (!Schema::hasColumn('billing_subscription_periodes', 'billing_periode')) {
                $table->string('billing_periode')->nullable()->after('billing_periode_id');
            }

            if (!Schema::hasColumn('billing_subscription_periodes', 'is_paid')) {
                $table->tinyInteger('is_paid')->default(0)->after('billing_periode');
            }

            if (!Schema::hasColumn('billing_subscription_periodes', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('is_paid');
            }

            if (!Schema::hasColumn('billing_subscription_periodes', 'status')) {
                $table->enum('status', ['paid', 'expired', 'unpaid'])
                    ->default('unpaid')
                    ->after('is_paid');
            }
        });
    }
};
