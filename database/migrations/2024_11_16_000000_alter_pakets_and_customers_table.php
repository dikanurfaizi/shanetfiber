<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan softDeletes di tabel pakets jika belum ada
        if (Schema::hasTable('pakets')) {
            Schema::table('pakets', function (Blueprint $table) {
                if (!Schema::hasColumn('pakets', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // 2. Modifikasi tabel customers
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                // Tambahkan kolom baru jika belum ada
                if (!Schema::hasColumn('customers', 'simpleque_id')) {
                    $table->string('simpleque_id')->nullable();
                }
                if (!Schema::hasColumn('customers', 'simpleque_name')) {
                    $table->string('simpleque_name')->nullable();
                }
                if (!Schema::hasColumn('customers', 'ip_static')) {
                    $table->string('ip_static')->nullable();
                }
                if (!Schema::hasColumn('customers', 'mac_address')) {
                    $table->string('mac_address')->nullable();
                }
                if (!Schema::hasColumn('customers', 'interface')) {
                    $table->string('interface')->nullable();
                }
                if (!Schema::hasColumn('customers', 'arp_id')) {
                    $table->string('arp_id')->nullable();
                }
                if (!Schema::hasColumn('customers', 'address_list_id')) {
                    $table->string('address_list_id')->nullable();
                }

                // Tambahkan softDeletes jika belum ada
                if (!Schema::hasColumn('customers', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Ubah secret_id dan ppp_type_id menjadi nullable menggunakan SQL Statement langsung (Aman dari error Doctrine)
            if (Schema::hasColumn('customers', 'secret_id')) {
                DB::statement('ALTER TABLE customers MODIFY secret_id VARCHAR(255) NULL');
            }

            if (Schema::hasColumn('customers', 'ppp_type_id')) {
                DB::statement('ALTER TABLE customers MODIFY ppp_type_id BIGINT UNSIGNED NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert softDeletes di tabel pakets
        if (Schema::hasTable('pakets')) {
            Schema::table('pakets', function (Blueprint $table) {
                if (Schema::hasColumn('pakets', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }

        // Revert perubahan di tabel customers
        if (Schema::hasTable('customers')) {
            // Kembalikan secret_id dan ppp_type_id menjadi non-nullable via SQL Statement
            if (Schema::hasColumn('customers', 'secret_id')) {
                DB::statement('ALTER TABLE customers MODIFY secret_id VARCHAR(255) NOT NULL');
            }

            if (Schema::hasColumn('customers', 'ppp_type_id')) {
                DB::statement('ALTER TABLE customers MODIFY ppp_type_id BIGINT UNSIGNED NOT NULL');
            }

            Schema::table('customers', function (Blueprint $table) {
                // Hapus kolom baru jika ada
                if (Schema::hasColumn('customers', 'simpleque_id')) {
                    $table->dropColumn('simpleque_id');
                }
                if (Schema::hasColumn('customers', 'simpleque_name')) {
                    $table->dropColumn('simpleque_name');
                }
                if (Schema::hasColumn('customers', 'ip_static')) {
                    $table->dropColumn('ip_static');
                }
                if (Schema::hasColumn('customers', 'mac_address')) {
                    $table->dropColumn('mac_address');
                }
                if (Schema::hasColumn('customers', 'interface')) {
                    $table->dropColumn('interface');
                }
                if (Schema::hasColumn('customers', 'arp_id')) {
                    $table->dropColumn('arp_id');
                }
                if (Schema::hasColumn('customers', 'address_list_id')) {
                    $table->dropColumn('address_list_id');
                }

                // Hapus softDeletes
                if (Schema::hasColumn('customers', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
