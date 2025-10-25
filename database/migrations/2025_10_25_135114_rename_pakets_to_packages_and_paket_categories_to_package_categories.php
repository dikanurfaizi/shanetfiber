<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 🩵 Tambahkan baris ini
        Schema::getConnection()->getDoctrineSchemaManager()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');

        // ---- Step 1: Rename main tables ----
        if (Schema::hasTable('paket_categories')) {
            Schema::rename('paket_categories', 'package_categories');
        }

        if (Schema::hasTable('pakets')) {
            Schema::rename('pakets', 'packages');
        }

        // ---- Step 2: Adjust foreign keys ----
        $tablesToUpdate = [
            'packages',
            'customers',
            'customer_subscriptions',
            'subscription_durations'
        ];

        foreach ($tablesToUpdate as $tblName) {
            if (Schema::hasTable($tblName)) {
                // Drop foreign key lama & rename kolom
                Schema::table($tblName, function (Blueprint $table) use ($tblName) {
                    // Drop foreign key lama
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    $doctrineTable = $sm->listTableDetails($tblName);

                    if ($doctrineTable->hasForeignKey("{$tblName}_paket_id_foreign")) {
                        $table->dropForeign("{$tblName}_paket_id_foreign");
                    }

                    if ($doctrineTable->hasForeignKey("{$tblName}_paket_category_id_foreign")) {
                        $table->dropForeign("{$tblName}_paket_category_id_foreign");
                    }

                    // Rename kolom
                    if (Schema::hasColumn($tblName, 'paket_id')) {
                        $table->renameColumn('paket_id', 'package_id');
                    }

                    if (Schema::hasColumn($tblName, 'paket_category_id')) {
                        $table->renameColumn('paket_category_id', 'package_category_id');
                    }
                });

                // Tambahkan kembali foreign key baru
                Schema::table($tblName, function (Blueprint $table) use ($tblName) {
                    if (Schema::hasColumn($tblName, 'package_id')) {
                        DB::statement("ALTER TABLE `$tblName` MODIFY COLUMN `package_id` BIGINT UNSIGNED NULL");
                    }

                    if (Schema::hasColumn($tblName, 'package_id')) {
                        $table->foreign('package_id')
                            ->references('id')
                            ->on('packages')
                            ->cascadeOnUpdate()
                            ->nullOnDelete();
                    }

                    if (Schema::hasColumn($tblName, 'package_category_id')) {
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
        Schema::getConnection()->getDoctrineSchemaManager()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');

        // ---- Step 1: Revert table names ----
        if (Schema::hasTable('package_categories')) {
            Schema::rename('package_categories', 'paket_categories');
        }

        if (Schema::hasTable('packages')) {
            Schema::rename('packages', 'pakets');
        }

        // ---- Step 2: Revert foreign keys ----
        $tablesToUpdate = [
            'pakets',
            'customers',
            'customer_subscriptions',
            'subscription_durations'
        ];

        foreach ($tablesToUpdate as $tblName) {
            if (Schema::hasTable($tblName)) {
                // Drop FK baru dan rename balik
                Schema::table($tblName, function (Blueprint $table) use ($tblName) {
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    $doctrineTable = $sm->listTableDetails($tblName);

                    if ($doctrineTable->hasForeignKey("{$tblName}_package_id_foreign")) {
                        $table->dropForeign("{$tblName}_package_id_foreign");
                    }

                    if ($doctrineTable->hasForeignKey("{$tblName}_package_category_id_foreign")) {
                        $table->dropForeign("{$tblName}_package_category_id_foreign");
                    }

                    // Rename balik kolom
                    if (Schema::hasColumn($tblName, 'package_id')) {
                        $table->renameColumn('package_id', 'paket_id');
                    }

                    if (Schema::hasColumn($tblName, 'package_category_id')) {
                        $table->renameColumn('package_category_id', 'paket_category_id');
                    }
                });

                // Tambah lagi FK lama
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
