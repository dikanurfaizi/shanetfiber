<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Master add-ons
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->boolean('is_recurring')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add-on ownership per customer
        Schema::create('customer_addons', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('addon_id');
            $table->unsignedInteger('qty')->default(1);

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('addon_id')->references('id')->on('addons');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addons');
        Schema::dropIfExists('addons');
    }
};
