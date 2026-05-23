<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_users', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel users & banks
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Proteksi double input data yang sama
            $table->unique(['user_id', 'bank_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_users');
    }
};
