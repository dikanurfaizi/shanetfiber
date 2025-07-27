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
        Schema::create('billing_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();             // 'monthly', 'quarterly', etc
            $table->string('name');                       // 'Per Bulan', 'Per 3 Bulan'
            $table->unsignedTinyInteger('months');        // 1, 3, 6, 12
            $table->boolean('is_active')->default(true);  // bisa toggle jika tidak dipakai lagi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_cycles');
    }
};
