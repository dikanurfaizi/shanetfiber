<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('billing_addons', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->unsignedBigInteger('billing_id');
            $table->unsignedBigInteger('addon_id');

            $table->unsignedInteger('qty')->default(1);
            $table->decimal('price', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->timestamps();

            $table->foreign('billing_id')->references('id')->on('billings');
            $table->foreign('addon_id')->references('id')->on('addons');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_addons');
    }
};
