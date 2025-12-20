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
        | 1. Create billing_items if not exists
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('billing_items')) {
            Schema::create('billing_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('billing_id');
                $table->unsignedBigInteger('item_id')->nullable(); // FK ke addons (opsional)
                $table->string('item_name');                        // snapshot nama
                $table->unsignedInteger('qty')->default(1);
                $table->decimal('price', 12, 2);
                $table->decimal('subtotal', 12, 2);
                $table->timestamps();

                $table->foreign('billing_id')->references('id')->on('billings')->cascadeOnDelete();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Migrate old billing_addons → billing_items
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('billing_addons')) {
            $addons = DB::table('billing_addons')->get();

            foreach ($addons as $a) {
                // Ambil nama addon dari master addon table
                $addonInfo = DB::table('addons')->where('id', $a->addon_id)->first();

                DB::table('billing_items')->insert([
                    'billing_id' => $a->billing_id,
                    'item_id' => $a->addon_id,
                    'item_name' => $addonInfo->name ?? 'Addon',
                    'qty'       => $a->qty,
                    'price'     => $a->price,
                    'subtotal'  => $a->subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::dropIfExists('billing_addons');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_items');
    }
};
