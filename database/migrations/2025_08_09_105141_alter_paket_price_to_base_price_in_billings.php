<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('billings') && Schema::hasColumn('billings', 'paket_price')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->renameColumn('paket_price', 'base_price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('billings') && Schema::hasColumn('billings', 'base_price')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->renameColumn('base_price', 'paket_price');
            });
        }
    }
};
