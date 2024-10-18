<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('customer_onts')) {
            Schema::create('customer_onts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->string('merk_ont')->nullable();
                $table->string('type_ont')->nullable();
                $table->string('username')->nullable();
                $table->string('password')->nullable();
                $table->string('port')->nullable()->default(80);
                $table->string('mac_address')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_onts');
    }
};
