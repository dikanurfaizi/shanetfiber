<?php

//use DB;

use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Models\Midtran;
use App\Models\Customer;
use App\Models\Websystem;
use App\Models\PaymentGateway;
use App\Models\Receipt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Mikrotik;
use App\Models\Paket;
use App\Services\Routerboard\Ppp\SecretService;
use App\Http\Resources\Pppoe\ProfileResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rules\Unique;
//use Exception;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // private SecretService $secretService;

    // public function __construct(SecretService $secretService)
    //  {
    //     $this->secretService = $secretService;
    // }
    public function up(): void
    {



        //composer require doctrine/dbal
        //Update
        //======================================================================

        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_id')->unique()->nullable();
        });


        Schema::table('billings', function (Blueprint $table) {
            $table->integer('tax')->nullable();
        });

        Schema::table('websystems', function (Blueprint $table) {
            $table->enum('enable_tax', ['false', 'true'])->default('false');
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->string('email')->nullable();
            $table->string('url')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country_code')->nullable()->default('IDN');
            $table->string('phone')->nullable();
            $table->string('customer_code')->nullable();
            $table->integer('long_customer_code')->nullable();
        });


        Schema::table('billings', function (Blueprint $table) {
            if (Schema::hasColumn('billings', 'backup_pay_later')) {
                // return dd('ada');
                $table->dropColumn('backup_pay_later');
            }
        });
        Schema::table('billings', function (Blueprint $table) {
            $table->string('backup_pay_later')->nullable();
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
            $table->timestamp('pay_later')->nullable();
        });
        $billings = Billing::where('status', 'PL')->get();
        foreach ($billings as $billing) {
            $paylater = Carbon::parse($billing->backup_pay_later);
            $billing->update([
                'pay_later' => $paylater
            ]);
        }
        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn('backup_pay_later');
        });
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'backup_activation_date')) {
                $table->string('backup_activation_date');
            }
            if (Schema::hasColumn('customers', 'discount')) {
                $table->decimal('discount', 5, 2)->change()->nullable()->default(0);
            }
        });

        //Copy activation date
        $customers = Customer::all();
        foreach ($customers as $customer) {
            $customer->update([
                'backup_activation_date' => $customer->activation_date,
                'primary_server' => $customer->mikrotik_id,
            ]);
        }

        //Ubah type coloumn activation_date
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

        //Copy backup_activation_date to activation_date
        $customers = Customer::all();
        foreach ($customers as $customer) {
            $activationDate = Carbon::parse($customer->backup_activation_date);
            $customer->update([
                'activation_date' => $activationDate,
            ]);
        }

        //hapus backup_activation_date
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('backup_activation_date');
        });

        Schema::table('billings', function (Blueprint $table) {
            if (Schema::hasColumn('billings', 'payment_time')) {
                $table->timestamp('payment_time')->change()->nullable();
            }
            $table->integer('receipt_id')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_time')) {
                $table->timestamp('payment_time')->change()->nullable();
            }
        });

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
        // Schema::table('receipts', function (Blueprint $table) {
        //     $table->integer('tax')->nullable();
        // });
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->string('bank');
            $table->string('account_number');
            $table->enum('disabled', ['false', 'true'])->default('true');
            $table->timestamps();
        });

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

        $customers = Customer::get();
        foreach ($customers as $customer) {
            if ($customer) {
                $billings = Billing::where('customer_id', $customer->id)->get();
                foreach ($billings as $billing) {
                    $dueDate = Carbon::parse($customer->activation_date)->format('d') . ' ' . Carbon::parse($billing->billing_periode->name)->format('F Y');
                    $billing->update([
                        'due_date' => Carbon::parse($dueDate),
                    ]);
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


        $billingsPeriode = BillingPeriode::all();
        foreach ($billingsPeriode as $billingPeriode) {
            $billingPeriode->update([
                'month' => Carbon::parse($billingPeriode->name)->format('F'),
                'year' => Carbon::parse($billingPeriode->name)->format('Y'),
            ]);
        }

        $billings = Billing::where('status', 'LS')->where('paket_price', '!=', 0)->get();
        foreach ($billings as $billing) {
            $getLastReceipt = Receipt::latest('created_at')->value('number');
            if (!$getLastReceipt) {
                $numberReceipt = 1;
            } else {
                $receipt = explode('-', $getLastReceipt);
                $numberReceipt = (int)$receipt[1];
                $numberReceipt++;
            }

            $receipt = Receipt::create([
                'slug' => $billing->slug,
                'periode' => $billing->billing_periode->name,
                'number' => str('KWT-' . sprintf("%05d", $numberReceipt) . '-' . Carbon::parse($billing->payment_time)->format('F') . '-' . Carbon::parse($billing->payment_time)->format('Y')),
                'billing_number' => $billing->billing_number,
                'customer_id' => $billing->id,
                'customer_name' => $billing->customer_name,
                'customer_address' => $billing->customer->address,
                'paket_name' => $billing->paket_name,
                'paket_price' => $billing->paket_price,
                'created_name' => auth()->user()->name,
                'teller_name' => auth()->user()->name,
                'payment_time' => $billing->payment_time,
                'payment_methode' => 'cash',
            ]);
            $newReceipt = $receipt->fresh();
            $billing->update([
                'receipt_id' => $newReceipt->id,
            ]);
        }

        DB::statement("ALTER TABLE `auto_isolirs` CHANGE `proxy_access_action` `proxy_access_action` ENUM('redirect','deny','allow') NOT NULL DEFAULT 'deny';");

        Schema::table('auto_isolirs', function (Blueprint $table) {
            if (!Schema::hasColumn('auto_isolirs', 'firewall_filter_id')) {
                $table->string('firewall_filter_id')->default('0');
            }
        });

        Artisan::call('db:seed --class=AdminPermissionTableSeeder');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
