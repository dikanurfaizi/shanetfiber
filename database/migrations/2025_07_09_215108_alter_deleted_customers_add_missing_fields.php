<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deleted_customers', function (Blueprint $table) {
            $table->string('internet_service')->nullable()->after('mikrotik_id');
            $table->string('simpleque_id')->nullable()->after('ppp_type_id');
            $table->string('simpleque_name')->nullable()->after('simpleque_id');
            $table->string('ip_static')->nullable()->after('simpleque_name');
            $table->string('mac_address')->nullable()->after('ip_static');
            $table->string('interface')->nullable()->after('mac_address');
            $table->string('arp_id')->nullable()->after('interface');
            $table->string('address_list_id')->nullable()->after('arp_id');
            $table->enum('disabled', ['false', 'true'])->default('true')->after('contact');
            $table->enum('activation', ['false', 'true'])->default('false')->after('disabled');
            $table->timestamp('deleted_at')->nullable()->after('updated_at');
            $table->bigInteger('primary_server')->nullable()->after('deleted_at');
            $table->decimal('discount', 10, 2)->nullable()->after('primary_server');
            $table->string('customer_id')->nullable()->after('discount');
            $table->timestamp('activation_date')->nullable()->change(); // override varchar ke timestamp
            $table->string('caller_id')->nullable()->after('activation_date');
            $table->unsignedBigInteger('user_id')->nullable()->after('caller_id');
            $table->unsignedBigInteger('member_id')->nullable()->after('user_id');
            $table->unsignedBigInteger('billing_option')->nullable()->after('member_id');
            $table->string('billing_period')->nullable()->after('billing_option');
            $table->bigInteger('id')->unsigned()->change();
        });

        DB::statement('ALTER TABLE deleted_customers MODIFY id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        Schema::table('deleted_customers', function (Blueprint $table) {
            $table->dropColumn([
                'internet_service',
                'simpleque_id',
                'simpleque_name',
                'ip_static',
                'mac_address',
                'interface',
                'arp_id',
                'address_list_id',
                'disabled',
                'activation',
                'deleted_at',
                'primary_server',
                'discount',
                'customer_id',
                'caller_id',
                'user_id',
                'member_id',
                'billing_option',
                'billing_period',
            ]);

            // Revert activation_date back to string if needed
            $table->string('activation_date')->nullable()->change();
        });
    }
};
