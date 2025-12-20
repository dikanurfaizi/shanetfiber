<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_items', function (Blueprint $table) {
            $table->id();

            // === RELATION ===
            $table->foreignId('receipt_id')
                ->constrained('receipts')
                ->cascadeOnDelete();

            // === ITEM TYPE ===
            $table->enum('type', ['package', 'addon'])
                ->comment('Item type: package or addon');

            // Optional reference (for trace only, NOT FK)
            $table->unsignedBigInteger('ref_id')
                ->nullable()
                ->comment('Reference ID (package_id / addon_id), not enforced');

            // === SNAPSHOT DATA ===
            $table->string('name');
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);

            // Flexible future data
            $table->json('meta')->nullable();

            $table->timestamps();

            // === INDEXES ===
            $table->index(['receipt_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_items');
    }
};
