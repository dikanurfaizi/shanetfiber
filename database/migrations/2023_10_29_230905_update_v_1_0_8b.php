<?php

use App\Models\Billings\Billing;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use App\Models\Receipt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah customer_id pada tabel customers jika belum ada
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'customer_id')) {
                $table->string('customer_id')->unique()->nullable();
            }
        });

        // 2. Tambah tax pada tabel billings jika belum ada
        Schema::table('billings', function (Blueprint $table) {
            if (!Schema::hasColumn('billings', 'tax')) {
                $table->integer('tax')->nullable();
            }
        });

        // 3. Tambah konfigurasi sistem & pajak pada websystems
        Schema::table('websystems', function (Blueprint $table) {
            if (!Schema::hasColumn('websystems', 'enable_tax')) {
                $table->enum('enable_tax', ['false', 'true'])->default('false');
            }
            if (!Schema::hasColumn('websystems', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('websystems', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('websystems', 'url')) {
                $table->string('url')->nullable();
            }
            if (!Schema::hasColumn('websystems', 'address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('websystems', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('websystems', 'postal_code')) {
                $table->string('postal_code')->nullable();
            }
            if (!Schema::hasColumn('websystems', 'country_code')) {
                $table->string('country_code')->nullable()->default('IDN');
            }
            if (!Schema::hasColumn('websystems', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('websystems', 'customer_code')) {
                $table->string('customer_code')->nullable();
            }
            if (!Schema::hasColumn('websystems', 'long_customer_code')) {
                $table->integer('long_customer_code')->nullable();
            }
        });

        // 4. Migrasi data pay_later pada billings
        Schema::table('billings', function (Blueprint $table) {
            if (!Schema::hasColumn('billings', 'backup_pay_later')) {
                $table->string('backup_pay_later')->nullable();
            }
        });

        $billings = Billing::where('status', 'PL')->get();
        foreach ($billings as $billing) {
            $billing->update([
                'backup_pay_later' => $billing->pay_later
            ]);
        }

        Schema::table('billings', function (Blueprint $table) {
            if (Schema::hasColumn('billings', 'pay_later')) {
                $table->dropColumn('pay_later');
            }
        });

        Schema::table('billings', function (Blueprint $table) {
            if (!Schema::hasColumn('billings', 'pay_later')) {
                $table->timestamp('pay_later')->nullable();
            }
        });

        $billings = Billing::where('status', 'PL')->get();
        foreach ($billings as $billing) {
            if (!empty($billing->backup_pay_later)) {
                $paylater = Carbon::parse($billing->backup_pay_later);
                $billing->update([
                    'pay_later' => $paylater
                ]);
            }
        }

        Schema::table('billings', function (Blueprint $table) {
            if (Schema::hasColumn('billings', 'backup_pay_later')) {
                $table->dropColumn('backup_pay_later');
            }
        });

        // 5. Penyesuaian kolom pada customers
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'backup_activation_date')) {
                $table->string('backup_activation_date')->nullable();
            }
            if (!Schema::hasColumn('customers', 'primary_server')) {
                $table->unsignedBigInteger('primary_server')->nullable();
            }
            if (!Schema::hasColumn('customers', 'discount')) {
                $table->decimal('discount', 5, 2)->nullable()->default(0);
            }
        });

        // Copy activation date & mikrotik_id ke primary_server
        $customers = Customer::all();
        foreach ($customers as $customer) {
            $customer->update([
                'backup_activation_date' => $customer->activation_date,
                'primary_server' => $customer->mikrotik_id ?? null,
            ]);
        }

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'activation_date')) {
                $table->dropColumn('activation_date');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'activation_date')) {
                $table->timestamp('activation_date')->nullable();
            }
            if (!Schema::hasColumn('customers', 'caller_id')) {
                $table->string('caller_id')->nullable();
            }
        });

        $customers = Customer::all();
        foreach ($customers as $customer) {
            if (!empty($customer->backup_activation_date)) {
                $activationDate = Carbon::parse($customer->backup_activation_date);
                $customer->update([
                    'activation_date' => $activationDate,
                ]);
            }
        }

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'backup_activation_date')) {
                $table->dropColumn('backup_activation_date');
            }
        });

        // 6. Penyesuaian billing payment_time & receipt_id
        Schema::table('billings', function (Blueprint $table) {
            if (!Schema::hasColumn('billings', 'payment_time')) {
                $table->timestamp('payment_time')->nullable();
            }
            if (!Schema::hasColumn('billings', 'receipt_id')) {
                $table->integer('receipt_id')->nullable();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_time')) {
                $table->timestamp('payment_time')->nullable();
            }
        });

        // 7. Buat tabel receipts jika belum ada
        if (!Schema::hasTable('receipts')) {
            Schema::create('receipts', function (Blueprint $table) {
                $table->id();
                $table->string('slug');
                $table->string('periode');
                $table->string('number');
                $table->string('billing_number');
                $table->integer('customer_id');
                $table->string('customer_name');
                $table->string('customer_address')->nullable();
                $table->string('paket_name');
                $table->integer('paket_price');
                $table->integer('tax')->nullable();
                $table->string('created_name');
                $table->string('teller_name')->nullable();
                $table->timestamp('payment_time')->nullable();
                $table->string('payment_methode')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('account_name')->nullable();
                $table->string('account_number')->nullable();
                $table->string('deposit_bank_name')->nullable();
                $table->string('deposit_account_name')->nullable();
                $table->string('deposit_account_number')->nullable();
                $table->string('reference_id')->nullable();
                $table->timestamps();
            });
        }

        // 8. Buat tabel banks jika belum ada
        if (!Schema::hasTable('banks')) {
            Schema::create('banks', function (Blueprint $table) {
                $table->id();
                $table->string('slug');
                $table->string('name');
                $table->string('bank');
                $table->string('account_number');
                $table->enum('disabled', ['false', 'true'])->default('true');
                $table->timestamps();
            });
        }

        // 9. Update auto_isolirs
        if (Schema::hasTable('auto_isolirs')) {
            Schema::table('auto_isolirs', function (Blueprint $table) {
                if (!Schema::hasColumn('auto_isolirs', 'due_date')) {
                    $table->string('due_date')->nullable();
                }
            });
            DB::statement("ALTER TABLE `auto_isolirs` CHANGE `proxy_access_action` `proxy_access_action` ENUM('redirect','deny','allow') NOT NULL DEFAULT 'deny';");

            Schema::table('auto_isolirs', function (Blueprint $table) {
                if (!Schema::hasColumn('auto_isolirs', 'firewall_filter_id')) {
                    $table->string('firewall_filter_id')->default('0');
                }
            });
        }

        // 10. Perbarui due_date pada billings
        Schema::table('billings', function (Blueprint $table) {
            if (Schema::hasColumn('billings', 'year')) {
                $table->dropColumn('year');
            }
            if (Schema::hasColumn('billings', 'month')) {
                $table->dropColumn('month');
            }
            if (!Schema::hasColumn('billings', 'due_date')) {
                $table->timestamp('due_date')->nullable();
            }
        });

        $customers = Customer::get();
        foreach ($customers as $customer) {
            if ($customer && !empty($customer->activation_date)) {
                $billings = Billing::where('customer_id', $customer->id)->get();
                foreach ($billings as $billing) {
                    if ($billing->billing_periode) {
                        $dueDate = Carbon::parse($customer->activation_date)->format('d') . ' ' . Carbon::parse($billing->billing_periode->name)->format('F Y');
                        $billing->update([
                            'due_date' => Carbon::parse($dueDate),
                        ]);
                    }
                }
            }
        }

        // 11. Perbarui periode bulan & tahun
        Schema::table('billing_periodes', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_periodes', 'year')) {
                $table->string('year')->nullable();
            }
            if (!Schema::hasColumn('billing_periodes', 'month')) {
                $table->string('month')->nullable();
            }
        });

        $billingsPeriode = BillingPeriode::all();
        foreach ($billingsPeriode as $billingPeriode) {
            if (!empty($billingPeriode->name)) {
                $billingPeriode->update([
                    'month' => Carbon::parse($billingPeriode->name)->format('F'),
                    'year' => Carbon::parse($billingPeriode->name)->format('Y'),
                ]);
            }
        }

        // 12. Generate Kuitansi untuk billing yang lunas (LS)
        $paidBillings = Billing::where('status', 'LS')->where('paket_price', '!=', 0)->get();
        foreach ($paidBillings as $billing) {
            $getLastReceipt = Receipt::latest('created_at')->value('number');
            if (!$getLastReceipt) {
                $numberReceipt = 1;
            } else {
                $receiptParts = explode('-', $getLastReceipt);
                $numberReceipt = isset($receiptParts[1]) ? (int)$receiptParts[1] + 1 : 1;
            }

            $paymentTime = $billing->payment_time ? Carbon::parse($billing->payment_time) : now();

            $receipt = Receipt::create([
                'slug' => $billing->slug,
                'periode' => optional($billing->billing_periode)->name ?? '-',
                'number' => 'KWT-' . sprintf("%05d", $numberReceipt) . '-' . $paymentTime->format('F') . '-' . $paymentTime->format('Y'),
                'billing_number' => $billing->billing_number,
                'customer_id' => $billing->customer_id,
                'customer_name' => $billing->customer_name,
                'customer_address' => optional($billing->customer)->address,
                'paket_name' => $billing->paket_name,
                'paket_price' => $billing->paket_price,
                'created_name' => 'System Administrator',
                'teller_name' => 'System Administrator',
                'payment_time' => $paymentTime,
                'payment_methode' => 'cash',
            ]);

            $billing->update([
                'receipt_id' => $receipt->id,
            ]);
        }

        // 13. Jalankan Seeder Admin Permission
        Artisan::call('db:seed', ['--class' => 'AdminPermissionTableSeeder', '--force' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
