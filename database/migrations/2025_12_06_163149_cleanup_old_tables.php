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
        |   → Ditambah sebelum snapshot di-drop
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
        $billings = DB::table('billings')->select('id', 'created_name')->get();

        foreach ($billings as $b) {

            if (!$b->created_name) {
                continue; // billing dibuat sistem → created_by tetap null
            }

            // Cari user berdasarkan nama lama
            $user = DB::table('users')
                ->where('name', $b->created_name)
                ->first();

            DB::table('billings')
                ->where('id', $b->id)
                ->update([
                    'created_by' => $user->id ?? null
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 3. Finalisasi ENUM status_new → status
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('billings', 'status_new')) {

            // Drop status lama
            if (Schema::hasColumn('billings', 'status')) {
                Schema::table('billings', function (Blueprint $table) {
                    $table->dropColumn('status');
                });
            }

            // Rename status_new → status
            Schema::table('billings', function (Blueprint $table) {
                $table->renameColumn('status_new', 'status');
            });
        }


        /*
        |--------------------------------------------------------------------------
        | 4. Drop kolom snapshot lama → setelah migrasi data aman
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
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        /*
    |--------------------------------------------------------------------------
    | 1. Tambah kembali snapshot columns (opsional)
    |--------------------------------------------------------------------------
    */
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

        /*
    |--------------------------------------------------------------------------
    | 2. STEP TERPISAH — TAMBAH status_old
    |--------------------------------------------------------------------------
    */
        if (
            Schema::hasColumn('billings', 'status') &&
            !Schema::hasColumn('billings', 'status_old')
        ) {
            Schema::table('billings', function (Blueprint $table) {
                $table->enum('status_old', ['BL', 'PL', 'LS', 'NP'])
                    ->nullable()
                    ->after('status');
            });
        }

        /*
    |--------------------------------------------------------------------------
    | 3. STEP TERPISAH — drop status, rename status_old → status
    |--------------------------------------------------------------------------
    */
        if (Schema::hasColumn('billings', 'status_old')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->dropColumn('status');
                $table->renameColumn('status_old', 'status');
            });
        }
    }
};
