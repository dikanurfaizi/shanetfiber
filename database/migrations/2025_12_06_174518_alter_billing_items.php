<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('billing_items', function (Blueprint $table) {

            // 1. Tambahkan kolom baru
            if (!Schema::hasColumn('billing_items', 'item_type')) {
                $table->enum('item_type', ['addon', 'product', 'service', 'manual'])
                    ->default('addon')
                    ->after('billing_id');
            }

            if (!Schema::hasColumn('billing_items', 'item_id')) {
                $table->unsignedBigInteger('item_id')->nullable()->after('item_type');
            }

            // 2. Pastikan snapshot fields ada
            if (!Schema::hasColumn('billing_items', 'item_name')) {
                $table->string('item_name')->after('item_id');
            }

            if (!Schema::hasColumn('billing_items', 'qty')) {
                $table->unsignedInteger('qty')->default(1)->after('item_name');
            }

            if (!Schema::hasColumn('billing_items', 'price')) {
                $table->decimal('price', 12, 2)->default(0)->after('qty');
            }

            if (!Schema::hasColumn('billing_items', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_items', function (Blueprint $table) {
            $table->dropColumn([
                'item_type',
                'item_id',
                'item_name',
                'qty',
                'price',
                'subtotal'
            ]);
        });
    }
};
