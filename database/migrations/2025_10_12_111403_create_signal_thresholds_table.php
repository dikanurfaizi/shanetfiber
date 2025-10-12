<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signal_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // contoh: "signal-too-strong"
            $table->string('label');          // contoh: "Signal Too Strong"
            $table->float('min_value')->nullable(); // batas bawah, contoh: -15
            $table->float('max_value')->nullable(); // batas atas, contoh: -1
            $table->string('description')->nullable(); // misal: "Too Strong (>-15 dBm)"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signal_thresholds');
    }
};
