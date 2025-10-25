<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            if (Schema::hasColumn('receipts', 'paket_name')) {
                $table->renameColumn('paket_name', 'package_name');
            }

            if (Schema::hasColumn('receipts', 'paket_price')) {
                $table->renameColumn('paket_price', 'package_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            if (Schema::hasColumn('receipts', 'package_name')) {
                $table->renameColumn('package_name', 'paket_name');
            }

            if (Schema::hasColumn('receipts', 'package_price')) {
                $table->renameColumn('package_price', 'paket_price');
            }
        });
    }
};
