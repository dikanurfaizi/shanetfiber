<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop billing_cycle_id dari pakets jika ada
        if (Schema::hasTable('pakets') && Schema::hasColumn('pakets', 'billing_cycle_id')) {
            try {
                DB::statement('ALTER TABLE pakets DROP FOREIGN KEY pakets_billing_cycle_id_foreign');
            } catch (\Exception $e) {
                // ignore
            }

            Schema::table('pakets', function (Blueprint $table) {
                if (Schema::hasColumn('pakets', 'billing_cycle_id')) {
                    $table->dropColumn('billing_cycle_id');
                }
            });
        }

        // 2. Drop subscription_duration_id di customer_subscriptions kalau ada
        if (Schema::hasTable('customer_subscriptions') && Schema::hasColumn('customer_subscriptions', 'subscription_duration_id')) {
            try {
                DB::statement('ALTER TABLE customer_subscriptions DROP FOREIGN KEY customer_subscriptions_subscription_duration_id_foreign');
            } catch (\Exception $e) {
                // ignore
            }

            Schema::table('customer_subscriptions', function (Blueprint $table) {
                if (Schema::hasColumn('customer_subscriptions', 'subscription_duration_id')) {
                    $table->dropColumn('subscription_duration_id');
                }
            });
        }

        // 3. subscription_durations
        if (!Schema::hasTable('subscription_durations')) {
            Schema::create('subscription_durations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('paket_id')->constrained('pakets')->onDelete('cascade');
                $table->string('name_en');
                $table->string('name_id');
                $table->unsignedTinyInteger('duration_months');
                $table->enum('discount_type', ['none', 'percent', 'amount'])->default('none');
                $table->unsignedDecimal('discount_value', 10, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. customer_subscriptions
        if (!Schema::hasTable('customer_subscriptions')) {
            Schema::create('customer_subscriptions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
                $table->foreignId('paket_id')->constrained('pakets')->onDelete('cascade');
                $table->foreignId('subscription_duration_id')->constrained('subscription_durations')->onDelete('cascade');
                $table->enum('status', ['active', 'paused', 'cancelled', 'terminated'])->default('active');
                $table->date('start_date');
                $table->date('end_date');
                $table->unsignedDecimal('price_final', 12, 2);
                $table->enum('discount_type_final', ['none', 'percent', 'amount'])->default('none');
                $table->unsignedDecimal('discount_value_final', 10, 2)->default(0);
                $table->timestamps();
            });
        }

        // 5. billing_subscription_periodes
        if (!Schema::hasTable('billing_subscription_periodes')) {
            Schema::create('billing_subscription_periodes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->foreignId('customer_subscription_id')->constrained('customer_subscriptions')->onDelete('cascade');
                $table->foreignId('billing_periode_id')->constrained('billing_periodes')->onDelete('cascade');
                $table->string('billing_periode')->nullable();
                $table->boolean('is_paid')->default(false);
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }

        // 6. Kolom customer_subscription_id di billings
        if (Schema::hasTable('billings') && !Schema::hasColumn('billings', 'customer_subscription_id')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_subscription_id')->nullable()->after('customer_id');
                $table->foreign('customer_subscription_id')->references('id')->on('customer_subscriptions')->onDelete('set null');
            });
        }

        // 7. Migrasi data dari billings ke customer_subscriptions dan billing_subscription_periodes
        $billings = DB::table('billings')->select('id', 'customer_id', 'paket_name', 'paket_price', 'status', 'due_date', 'payment_time', 'pay_later', 'billing_periode_id')->get();

        foreach ($billings as $bill) {
            $paket = DB::table('pakets')->where('name', $bill->paket_name)->first();
            if (!$paket) {
                continue;
            }

            // durasi default 1 bulan
            $duration = DB::table('subscription_durations')
                ->where('paket_id', $paket->id)
                ->where('duration_months', 1)
                ->first();

            if (!$duration) {
                $durationId = DB::table('subscription_durations')->insertGetId([
                    'paket_id' => $paket->id,
                    'name_en' => 'Monthly',
                    'name_id' => 'Per Bulan',
                    'duration_months' => 1,
                    'discount_type' => 'none',
                    'discount_value' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $durationId = $duration->id;
            }

            $startDate = $bill->due_date
                ? Carbon::parse($bill->due_date)->subMonth()
                : now()->subMonth();

            $endDate = $bill->due_date
                ? Carbon::parse($bill->due_date)
                : now();

            if (!Schema::hasTable('customer_subscriptions') || !Schema::hasColumn('customer_subscriptions', 'subscription_duration_id')) {
                // Jika tabel atau kolom tidak ada, hentikan migrasi data
                return;
            }

            $subscription = DB::table('customer_subscriptions')
                ->where('customer_id', $bill->customer_id)
                ->where('paket_id', $paket->id)
                ->where('subscription_duration_id', $durationId)
                ->first();

            if (!$subscription) {
                $subscriptionId = DB::table('customer_subscriptions')->insertGetId([
                    'customer_id' => $bill->customer_id,
                    'paket_id' => $paket->id,
                    'subscription_duration_id' => $durationId,
                    'status' => 'active',
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'price_final' => $bill->paket_price,
                    'discount_type_final' => 'none',
                    'discount_value_final' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $subscriptionId = $subscription->id;
            }

            DB::table('billings')
                ->where('id', $bill->id)
                ->update(['customer_subscription_id' => $subscriptionId]);

            // Migrasi data ke billing_subscription_periodes
            $periode = DB::table('billing_periodes')
                ->select('id', 'name')
                ->where('id', $bill->billing_periode_id)
                ->first();

            if ($bill->status == 'LS') {
                $isPaid = true;
                $paymentTime = $bill->payment_time;
            } else {
                $isPaid = false;
                $paymentTime = $bill->pay_later;
            }

            if ($periode) {
                DB::table('billing_subscription_periodes')->insert([
                    'customer_subscription_id' => $subscriptionId,
                    'billing_periode_id' => $periode->id,
                    'billing_periode' => $periode->name,
                    'is_paid' => (bool) $isPaid,
                    'paid_at' => $paymentTime,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Hapus foreign key & kolom customer_subscription_id dari billings
        if (Schema::hasTable('billings') && Schema::hasColumn('billings', 'customer_subscription_id')) {
            Schema::table('billings', function (Blueprint $table) {
                try {
                    $table->dropForeign(['customer_subscription_id']);
                } catch (\Exception $e) {
                    // ignore
                }
                if (Schema::hasColumn('billings', 'customer_subscription_id')) {
                    $table->dropColumn('customer_subscription_id');
                }
            });
        }

        // Hapus data dependent billing_subscription_periodes
        if (Schema::hasTable('billing_subscription_periodes') && Schema::hasTable('customer_subscriptions')) {
            try {
                DB::table('billing_subscription_periodes')
                    ->whereIn('customer_subscription_id', DB::table('customer_subscriptions')->pluck('id'))
                    ->delete();
            } catch (\Exception $e) {
                // ignore
            }
        }

        // Drop tabel anak dulu
        if (Schema::hasTable('billing_subscription_periodes')) {
            Schema::dropIfExists('billing_subscription_periodes');
        }
        if (Schema::hasTable('customer_subscriptions')) {
            Schema::dropIfExists('customer_subscriptions');
        }
        if (Schema::hasTable('subscription_durations')) {
            Schema::dropIfExists('subscription_durations');
        }
    }
};
