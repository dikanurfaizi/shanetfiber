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
        if (!Schema::hasTable('websystems')) {
            Schema::create('websystems', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique()->nullable();
                $table->string('title')->nullable(true);
                $table->string('version')->nullable(true);
                $table->string('cpanel_url')->nullable(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('websystems');
    }
};
