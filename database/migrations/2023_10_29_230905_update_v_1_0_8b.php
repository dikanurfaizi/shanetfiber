<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ======================================================================
        // Schema Updates
        // ======================================================================

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'customer_id')) {
                $table->string('customer_id')->unique()->nullable();
            }
        });

        Schema::table('billings', function (Blueprint $table) {
            if (!Schema::hasColumn('billings', 'tax')) {
                $table->integer('tax')->nullable();
            }
        });

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

        // ======================================================================
        // Handling pay_later Column in Billings
        // ======================================================================
        Schema::table('billings', function (Blueprint $table) {
            if (Schema::hasColumn('billings', 'backup_pay_later')) {
                $table->dropColumn('backup_pay_later');
            }
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->string('backup_pay_later')->nullable();
        });

        // Menggunakan DB Facade bukan Eloquent Billing Model
        $billingsPL = DB::table('billings')->where('status', 'PL')->get();
        foreach ($billingsPL as $billing) {
            DB::table('billings')->where('id', $billing->id)->update([
                'backup_pay_later' => $billing->pay_later ?? null
            ]);
        }

        Schema::table('billings', function (Blueprint $table) {
            if (Schema::hasColumn('billings', 'pay_later')) {
                $table->dropColumn('pay_later');
            }
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->timestamp('pay_later')->nullable();
        });

        $billingsPLBackup = DB::table('billings')->where('status', 'PL')->get();
        foreach ($billingsPLBackup as $billing) {
            $paylater = $billing->backup_pay_later ? Carbon::parse($billing->backup_pay_later) : null;
            DB::table('billings')->where('id', $billing->id)->update([
                'pay_later' => $paylater
            ]);
        }

        Schema::table('billings', function (Blueprint $table) {
            if (Schema::hasColumn('billings', 'backup_pay_later')) {
                $table->dropColumn('backup_pay_later');
            }
        });

        // ======================================================================
        // Handling activation_date & discount in Customers
        // ======================================================================
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'backup_activation_date')) {
                $table->string('backup_activation_date')->nullable();
            }
            if (Schema::hasColumn('customers', 'discount')) {
                $table->decimal('discount', 5, 2)->change()->nullable()->default(0);
            }
        });

        // Copy activation date via DB Facade
        $customers = DB::table('customers')->get();
        foreach ($customers as $customer) {
            DB::table('customers')->where('id', $customer->id)->update([
                'backup_activation_date' => $customer->activation_date ?? null,
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

        // Restore backup_activation_date to activation_date via DB Facade
        $customersBackup = DB::table('customers')->get();
        foreach ($customersBackup as $customer) {
            $activationDate = $customer->backup_activation_date ? Carbon::parse($customer->backup_activation_date) : null;
            DB::table('customers')->where('id', $customer->id)->update([
                'activation_date' => $activationDate,
            ]);
        }

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'backup_activation_date')) {
                $table->dropColumn('backup_activation_date');
            }
        });

        // ======================================================================
        // Additional Schema Updates
        // ======================================================================
        Schema::table('billings', function (Blueprint $table) {
            if (Schema::hasColumn('billings', 'payment_time')) {
                $table->timestamp('payment_time')->change()->nullable();
            }
            if (!Schema::hasColumn('billings', 'receipt_id')) {
                $table->integer('receipt_id')->nullable();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_time')) {
                $table->timestamp('payment_time')->change()->nullable();
            }
        });

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

        Schema::table('auto_isolirs', function (Blueprint $table) {
            if (Schema::hasColumn('auto_isolirs', 'due_date')) {
                $table->string('due_date')->change()->nullable();
            }
        });

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

        // Set due_date pada billings via DB Facade
        $allCustomers = DB::table('customers')->get();
        foreach ($allCustomers as $customer) {
            if ($customer && !empty($customer->activation_date)) {
                $customerBillings = DB::table('billings')->where('customer_id', $customer->id)->get();
                foreach ($customerBillings as $billing) {
                    $billingPeriode = DB::table('billing_periodes')->where('id', $billing->billing_periode_id ?? 0)->first();
                    $periodeName = $billingPeriode->name ?? null;

                    if ($periodeName) {
                        $dueDate = Carbon::parse($customer->activation_date)->format('d') . ' ' . Carbon::parse($periodeName)->format('F Y');
                        DB::table('billings')->where('id', $billing->id)->update([
                            'due_date' => Carbon::parse($dueDate),
                        ]);
                    }
                }
            }
        }

        Schema::table('billing_periodes', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_periodes', 'year')) {
                $table->string('year')->nullable();
            }
            if (!Schema::hasColumn('billing_periodes', 'month')) {
                $table->string('month')->nullable();
            }
        });

        $billingsPeriode = DB::table('billing_periodes')->get();
        foreach ($billingsPeriode as $billingPeriode) {
            if (!empty($billingPeriode->name)) {
                DB::table('billing_periodes')->where('id', $billingPeriode->id)->update([
                    'month' => Carbon::parse($billingPeriode->name)->format('F'),
                    'year' => Carbon::parse($billingPeriode->name)->format('Y'),
                ]);
            }
        }

        // Generate receipt via DB Facade (dengan safe fallback jika auth()->user() null)
        $creatorName = auth()->check() ? auth()->user()->name : 'System Installer';
        $paidBillings = DB::table('billings')->where('status', 'LS')->where('paket_price', '!=', 0)->get();

        foreach ($paidBillings as $billing) {
            $getLastReceipt = DB::table('receipts')->latest('created_at')->value('number');
            if (!$getLastReceipt) {
                $numberReceipt = 1;
            } else {
                $receiptArr = explode('-', $getLastReceipt);
                $numberReceipt = isset($receiptArr[1]) ? ((int)$receiptArr[1] + 1) : 1;
            }

            $customerData = DB::table('customers')->where('id', $billing->customer_id ?? 0)->first();
            $billingPeriode = DB::table('billing_periodes')->where('id', $billing->billing_periode_id ?? 0)->first();

            $receiptId = DB::table('receipts')->insertGetId([
                'slug' => $billing->slug ?? Str::slug($billing->billing_number ?? time()),
                'periode' => $billingPeriode->name ?? 'N/A',
                'number' => 'KWT-' . sprintf("%05d", $numberReceipt) . '-' . Carbon::parse($billing->payment_time ?? now())->format('F-Y'),
                'billing_number' => $billing->billing_number ?? '',
                'customer_id' => $billing->id,
                'customer_name' => $billing->customer_name ?? '',
                'customer_address' => $customerData->address ?? '',
                'paket_name' => $billing->paket_name ?? '',
                'paket_price' => $billing->paket_price ?? 0,
                'created_name' => $creatorName,
                'teller_name' => $creatorName,
                'payment_time' => $billing->payment_time ?? now(),
                'payment_methode' => 'cash',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('billings')->where('id', $billing->id)->update([
                'receipt_id' => $receiptId,
            ]);
        }

        DB::statement("ALTER TABLE `auto_isolirs` CHANGE `proxy_access_action` `proxy_access_action` ENUM('redirect','deny','allow') NOT NULL DEFAULT 'deny';");

        Schema::table('auto_isolirs', function (Blueprint $table) {
            if (!Schema::hasColumn('auto_isolirs', 'firewall_filter_id')) {
                $table->string('firewall_filter_id')->default('0');
            }
        });

        // PERBAIKAN: Pemanggilan Artisan Seeder wajib menggunakan format Array & '--force' => true
        Artisan::call('db:seed', [
            '--class' => 'AdminPermissionTableSeeder',
            '--force' => true
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
