<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('billings')) {
            Schema::table('billings', function (Blueprint $table) {
                if (Schema::hasColumn('billings', 'paket_name')) {
                    $table->dropColumn('paket_name');
                }
                if (Schema::hasColumn('billings', 'customer_name')) {
                    $table->dropColumn('customer_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('billings')) {
            Schema::table('billings', function (Blueprint $table) {
                if (!Schema::hasColumn('billings', 'paket_name')) {
                    $table->string('paket_name')->nullable()->after('paket_id');
                }
                if (!Schema::hasColumn('billings', 'customer_name')) {
                    $table->string('customer_name')->nullable()->after('customer_id');
                }
            });
        }
    }
};
