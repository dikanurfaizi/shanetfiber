<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ---- Step 1: Rename main tables ----
        if (Schema::hasTable('paket_categories') && !Schema::hasTable('package_categories')) {
            Schema::rename('paket_categories', 'package_categories');
        }

        if (Schema::hasTable('pakets') && !Schema::hasTable('packages')) {
            Schema::rename('pakets', 'packages');
        }

        // ---- Step 2: Adjust foreign keys & columns ----
        $tablesToUpdate = [
            'packages',
            'customers',
            'customer_subscriptions',
            'subscription_durations'
        ];

        foreach ($tablesToUpdate as $tblName) {
            if (Schema::hasTable($tblName)) {
                // 1. Drop foreign keys secara aman menggunakan SQL statement
                try {
                    DB::statement("ALTER TABLE `{$tblName}` DROP FOREIGN KEY `{$tblName}_paket_id_foreign`");
                } catch (\Throwable $e) {
                }

                try {
                    DB::statement("ALTER TABLE `{$tblName}` DROP FOREIGN KEY `{$tblName}_paket_category_id_foreign`");
                } catch (\Throwable $e) {
                }

                try {
                    DB::statement("ALTER TABLE `{$tblName}` DROP FOREIGN KEY `{$tblName}_package_id_foreign`");
                } catch (\Throwable $e) {
                }

                try {
                    DB::statement("ALTER TABLE `{$tblName}` DROP FOREIGN KEY `{$tblName}_package_category_id_foreign`");
                } catch (\Throwable $e) {
                }

                // 2. Rename kolom menggunakan Schema builder bawaan Laravel
                Schema::table($tblName, function (Blueprint $table) use ($tblName) {
                    if (Schema::hasColumn($tblName, 'paket_id') && !Schema::hasColumn($tblName, 'package_id')) {
                        $table->renameColumn('paket_id', 'package_id');
                    }

                    if (Schema::hasColumn($tblName, 'paket_category_id') && !Schema::hasColumn($tblName, 'package_category_id')) {
                        $table->renameColumn('paket_category_id', 'package_category_id');
                    }
                });

                // 3. Modifikasi tipe kolom dan pasang kembali foreign key baru
                Schema::table($tblName, function (Blueprint $table) use ($tblName) {
                    if (Schema::hasColumn($tblName, 'package_id')) {
                        $table->unsignedBigInteger('package_id')->nullable()->change();
                        $table->foreign('package_id')
                            ->references('id')
                            ->on('packages')
                            ->cascadeOnUpdate()
                            ->nullOnDelete();
                    }

                    if (Schema::hasColumn($tblName, 'package_category_id')) {
                        $table->unsignedBigInteger('package_category_id')->nullable()->change();
                        $table->foreign('package_category_id')
                            ->references('id')
                            ->on('package_categories')
                            ->cascadeOnUpdate()
                            ->nullOnDelete();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        // ---- Step 1: Revert table names ----
        if (Schema::hasTable('package_categories') && !Schema::hasTable('paket_categories')) {
            Schema::rename('package_categories', 'paket_categories');
        }

        if (Schema::hasTable('packages') && !Schema::hasTable('pakets')) {
            Schema::rename('packages', 'pakets');
        }

        // ---- Step 2: Revert foreign keys & columns ----
        $tablesToUpdate = [
            'pakets',
            'customers',
            'customer_subscriptions',
            'subscription_durations'
        ];

        foreach ($tablesToUpdate as $tblName) {
            if (Schema::hasTable($tblName)) {
                // Drop FK baru
                try {
                    DB::statement("ALTER TABLE `{$tblName}` DROP FOREIGN KEY `{$tblName}_package_id_foreign`");
                } catch (\Throwable $e) {
                }

                try {
                    DB::statement("ALTER TABLE `{$tblName}` DROP FOREIGN KEY `{$tblName}_package_category_id_foreign`");
                } catch (\Throwable $e) {
                }

                // Rename balik kolom
                Schema::table($tblName, function (Blueprint $table) use ($tblName) {
                    if (Schema::hasColumn($tblName, 'package_id') && !Schema::hasColumn($tblName, 'paket_id')) {
                        $table->renameColumn('package_id', 'paket_id');
                    }

                    if (Schema::hasColumn($tblName, 'package_category_id') && !Schema::hasColumn($tblName, 'paket_category_id')) {
                        $table->renameColumn('package_category_id', 'paket_category_id');
                    }
                });

                // Pasang kembali FK lama
                Schema::table($tblName, function (Blueprint $table) use ($tblName) {
                    if (Schema::hasColumn($tblName, 'paket_id')) {
                        $table->foreign('paket_id')
                            ->references('id')
                            ->on('pakets')
                            ->cascadeOnUpdate()
                            ->nullOnDelete();
                    }

                    if (Schema::hasColumn($tblName, 'paket_category_id')) {
                        $table->foreign('paket_category_id')
                            ->references('id')
                            ->on('paket_categories')
                            ->cascadeOnUpdate()
                            ->nullOnDelete();
                    }
                });
            }
        }
    }
};
