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
                if (!Schema::hasColumn('billings', 'subtotal')) {
                    $table->decimal('subtotal', 12, 2)->default(0)->after('paket_price');
                }
                if (!Schema::hasColumn('billings', 'discount_amount')) {
                    $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
                }
                if (!Schema::hasColumn('billings', 'tax_amount')) {
                    $table->decimal('tax_amount', 12, 2)->default(0)->after('discount_amount');
                }
                if (!Schema::hasColumn('billings', 'total_amount')) {
                    $table->decimal('total_amount', 12, 2)->default(0)->after('tax_amount');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('billings')) {
            Schema::table('billings', function (Blueprint $table) {
                if (Schema::hasColumn('billings', 'subtotal')) {
                    $table->dropColumn('subtotal');
                }
                if (Schema::hasColumn('billings', 'discount_amount')) {
                    $table->dropColumn('discount_amount');
                }
                if (Schema::hasColumn('billings', 'tax_amount')) {
                    $table->dropColumn('tax_amount');
                }
                if (Schema::hasColumn('billings', 'total_amount')) {
                    $table->dropColumn('total_amount');
                }
            });
        }
    }
};
