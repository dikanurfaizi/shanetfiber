<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Membership master table (if not exists)
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->enum('discount_type', ['none', 'percent', 'amount'])->default('none');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Customer membership history
        Schema::create('customer_memberships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('membership_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('membership_id')->references('id')->on('memberships');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_memberships');
        Schema::dropIfExists('memberships');
    }
};
