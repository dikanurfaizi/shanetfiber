<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Tambah kolom created_by & updated_by (nullable)
        |--------------------------------------------------------------------------
        */
        Schema::table('billings', function (Blueprint $table) {
            if (!Schema::hasColumn('billings', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('created_at');
            }

            if (!Schema::hasColumn('billings', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Migrasi created_name → created_by
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('billings', 'created_name')) {
            $billings = DB::table('billings')->select('id', 'created_name')->get();

            foreach ($billings as $b) {
                if (!$b->created_name) {
                    continue;
                }

                $user = DB::table('users')
                    ->where('name', $b->created_name)
                    ->first();

                DB::table('billings')
                    ->where('id', $b->id)
                    ->update([
                        'created_by' => $user->id ?? null
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Finalisasi ENUM status_new → status (Menggunakan SQL Statement Langsung)
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('billings', 'status_new')) {
            // Drop status lama jika ada
            if (Schema::hasColumn('billings', 'status')) {
                Schema::table('billings', function (Blueprint $table) {
                    $table->dropColumn('status');
                });
            }

            // Rename status_new → status menggunakan ALTER TABLE MODIFY yang bersih dari bug default value
            DB::statement("ALTER TABLE `billings` CHANGE `status_new` `status` ENUM('BL','PL','LS','PR','VD') NOT NULL DEFAULT 'BL'");
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Drop kolom snapshot lama & pasang Foreign Key
        |--------------------------------------------------------------------------
        */
        Schema::table('billings', function (Blueprint $table) {
            $drop = [
                'customer_name',
                'customer_address',
                'created_name',
                'teller_name',
                'receipt_id',
            ];

            foreach ($drop as $col) {
                if (Schema::hasColumn('billings', $col)) {
                    $table->dropColumn($col);
                }
            }

            // Tambahkan foreign key setelah data dimigrasi
            try {
                $table->foreign('created_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            } catch (\Throwable $e) {
            }

            try {
                $table->foreign('updated_by')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            } catch (\Throwable $e) {
            }
        });
    }

    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            if (!Schema::hasColumn('billings', 'customer_name')) {
                $table->string('customer_name')->nullable();
            }

            if (!Schema::hasColumn('billings', 'customer_address')) {
                $table->string('customer_address')->nullable();
            }

            if (!Schema::hasColumn('billings', 'created_name')) {
                $table->string('created_name')->nullable();
            }

            if (!Schema::hasColumn('billings', 'teller_name')) {
                $table->string('teller_name')->nullable();
            }

            if (!Schema::hasColumn('billings', 'receipt_id')) {
                $table->unsignedBigInteger('receipt_id')->nullable();
            }

            // Drop created_by & updated_by
            if (Schema::hasColumn('billings', 'created_by')) {
                try {
                    $table->dropForeign(['created_by']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('billings', 'updated_by')) {
                try {
                    $table->dropForeign(['updated_by']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('updated_by');
            }
        });

        // Revert status enum back if needed
        if (Schema::hasColumn('billings', 'status')) {
            DB::statement("ALTER TABLE `billings` CHANGE `status` `status` ENUM('BL','PL','LS','NP') NULL");
        }
    }
};
