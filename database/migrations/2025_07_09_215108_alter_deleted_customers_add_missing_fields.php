<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('deleted_customers')) {
            // 1. Tambahkan kolom satu per satu dengan pengecekan Schema::hasColumn (aman dari duplikasi)
            Schema::table('deleted_customers', function (Blueprint $table) {
                if (!Schema::hasColumn('deleted_customers', 'internet_service')) {
                    $table->string('internet_service')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'simpleque_id')) {
                    $table->string('simpleque_id')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'simpleque_name')) {
                    $table->string('simpleque_name')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'ip_static')) {
                    $table->string('ip_static')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'mac_address')) {
                    $table->string('mac_address')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'interface')) {
                    $table->string('interface')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'arp_id')) {
                    $table->string('arp_id')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'address_list_id')) {
                    $table->string('address_list_id')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'disabled')) {
                    $table->enum('disabled', ['false', 'true'])->default('true');
                }
                if (!Schema::hasColumn('deleted_customers', 'activation')) {
                    $table->enum('activation', ['false', 'true'])->default('false');
                }
                if (!Schema::hasColumn('deleted_customers', 'deleted_at')) {
                    $table->timestamp('deleted_at')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'primary_server')) {
                    $table->bigInteger('primary_server')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'discount')) {
                    $table->decimal('discount', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'customer_id')) {
                    $table->string('customer_id')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'activation_date')) {
                    $table->timestamp('activation_date')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'caller_id')) {
                    $table->string('caller_id')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'member_id')) {
                    $table->unsignedBigInteger('member_id')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'billing_option')) {
                    $table->unsignedBigInteger('billing_option')->nullable();
                }
                if (!Schema::hasColumn('deleted_customers', 'billing_period')) {
                    $table->string('billing_period')->nullable();
                }
            });

            // 2. Modifikasi tipe data kolom menggunakan DB::statement (Aman dari error Doctrine Schema Manager)
            if (Schema::hasColumn('deleted_customers', 'activation_date')) {
                DB::statement('ALTER TABLE deleted_customers MODIFY activation_date TIMESTAMP NULL');
            }

            if (Schema::hasColumn('deleted_customers', 'id')) {
                DB::statement('ALTER TABLE deleted_customers MODIFY id BIGINT UNSIGNED NOT NULL');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('deleted_customers')) {
            Schema::table('deleted_customers', function (Blueprint $table) {
                $columnsToDrop = [
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
                ];

                $existingColumns = array_filter($columnsToDrop, function ($col) {
                    return Schema::hasColumn('deleted_customers', $col);
                });

                if (!empty($existingColumns)) {
                    $table->dropColumn($existingColumns);
                }
            });

            // Revert activation_date kembali ke varchar/string jika rollback
            if (Schema::hasColumn('deleted_customers', 'activation_date')) {
                DB::statement('ALTER TABLE deleted_customers MODIFY activation_date VARCHAR(255) NULL');
            }
        }
    }
};
