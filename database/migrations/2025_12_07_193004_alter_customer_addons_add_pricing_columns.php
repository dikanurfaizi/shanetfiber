<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCustomerAddonsAddPricingColumns extends Migration
{
    public function up()
    {
        Schema::table('customer_addons', function (Blueprint $table) {
            // hapus slug kalau tidak dipakai
            if (Schema::hasColumn('customer_addons', 'slug')) {
                $table->dropColumn('slug');
            }

            // harga snapshot
            $table->decimal('unit_price', 10, 2)->after('qty')->default(0);

            // qty × unit_price
            $table->decimal('subtotal', 10, 2)->after('unit_price')->default(0);
        });
    }

    public function down()
    {
        Schema::table('customer_addons', function (Blueprint $table) {
            $table->string('slug')->nullable();

            $table->dropColumn('unit_price');
            $table->dropColumn('subtotal');
        });
    }
}
