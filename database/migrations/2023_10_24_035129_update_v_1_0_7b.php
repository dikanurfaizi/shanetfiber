<?php

//use DB;
use App\Models\Midtran;
use App\Models\Customers\Customer;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //Version
        //Update Midtrans
        if (Schema::hasTable('midtrans')) {
            Schema::table('midtrans', function (Blueprint $table) {
                if (!Schema::hasColumn('midtrans', 'slug')) {
                    $table->string('slug')->unique();
                }
                if (!Schema::hasColumn('midtrans', 'name')) {
                    $table->string('name');
                }
                if (!Schema::hasColumn('midtrans', 'midtrans_merchant_id')) {
                    $table->string('midtrans_merchant_id')->nullable();
                }
                if (!Schema::hasColumn('midtrans', 'midtrans_client_key')) {
                    $table->string('midtrans_client_key')->nullable();
                }
                if (!Schema::hasColumn('midtrans', 'midtrans_server_key')) {
                    $table->string('midtrans_server_key')->nullable();
                }
                if (!Schema::hasColumn('midtrans', 'midtrans_sandbox_client_key')) {
                    $table->string('midtrans_sandbox_client_key')->nullable();
                }
                if (!Schema::hasColumn('midtrans', 'midtrans_sandbox_server_key')) {
                    $table->string('midtrans_sandbox_server_key')->nullable();
                }
                if (!Schema::hasColumn('midtrans', 'midtrans_is_production')) {
                    $table->enum('midtrans_is_production', ['false', 'true'])->default('false');
                }
                if (!Schema::hasColumn('midtrans', 'disabled')) {
                    $table->enum('disabled', ['false', 'true'])->default('true');
                }
            });
        } else {
            Schema::create('midtrans', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->string('midtrans_merchant_id')->nullable();
                $table->string('midtrans_client_key')->nullable();
                $table->string('midtrans_server_key')->nullable();
                $table->string('midtrans_sandbox_client_key')->nullable();
                $table->string('midtrans_sandbox_server_key')->nullable();
                $table->enum('midtrans_is_production', ['false', 'true'])->default('false');
                $table->enum('disabled', ['false', 'true'])->default('true');
                $table->timestamps();
            });
        }

        $midtrans = Midtran::create([
            'name' => 'Midtrans',
            'slug' => 'midtrans',
        ]);

        // Update table customer
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'primary_server')) {
                $table->bigInteger('primary_server')->nullable();
            }

            if (!Schema::hasColumn('customers', 'discount')) {
                $table->decimal('discount', 5, 2)->default(0);
            }

            if (Schema::hasColumn('customers', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        $customers = Customer::all();
        foreach ($customers as $customer) {
            $customer->update([
                'primary_server' => $customer->mikrotik_id,
            ]);
        }


        //Update Billing Table
        Schema::table('billings', function (Blueprint $table) {
            if (!Schema::hasColumn('billings', 'pay_later')) {
                $table->string('pay_later')->nullable();
            }
        });
        DB::statement("ALTER TABLE `billings` CHANGE `status` `status` ENUM('BL','LS','PL') NOT NULL DEFAULT 'BL';");

        //Update Auto Isolir Table
        Schema::table('auto_isolirs', function (Blueprint $table) {
            if (!Schema::hasColumn('auto_isolirs', 'nat_id')) {
                $table->string('nat_id')->nullable();
            }
            if (!Schema::hasColumn('auto_isolirs', 'nat_dst_address')) {
                $table->string('nat_dst_address')->nullable();
            }
            if (!Schema::hasColumn('auto_isolirs', 'nat_src_address_list')) {
                $table->string('nat_src_address_list')->nullable();
            }

            if (!Schema::hasColumn('auto_isolirs', 'nat_dst_address_list')) {
                $table->string('nat_dst_address_list')->nullable();
            }

            if (!Schema::hasColumn('auto_isolirs', 'proxy_access_id')) {
                $table->string('proxy_access_id')->nullable();
            }
            if (!Schema::hasColumn('auto_isolirs', 'proxy_access_src_address')) {
                $table->string('proxy_access_src_address')->nullable();
            }
            if (!Schema::hasColumn('auto_isolirs', 'proxy_access_action_data')) {
                $table->string('proxy_access_action_data')->nullable();
            }
            if (!Schema::hasColumn('auto_isolirs', 'proxy_access_action')) {
                $table->enum('proxy_access_action', ['redirect', 'deny'])->default('redirect');
            }
        });

        //Add Payment Gateway Table
        if (!Schema::hasTable('payment_gateways')) {
            Schema::create('payment_gateways', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->enum('disabled', ['false', 'true'])->default('false');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        PaymentGateway::create([
            'name' => 'Midtrans',
            'slug' => 'midtrans',
            'description' => 'Midtrans Payment Gateway'
        ]);

        //Update web system table
        if (Schema::hasTable('websystems')) {
            Schema::table('websystems', function (Blueprint $table) {
                if (!Schema::hasColumn('websystems', 'payment_gateway')) {
                    $table->enum('payment_gateway', ['disable', 'enable'])->default('disable');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
