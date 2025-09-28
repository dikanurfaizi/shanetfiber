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
        Schema::create('olts', function (Blueprint $table) {
            $table->id(); // AUTO_INCREMENT primary key
            $table->string('name', 100)->nullable();
            $table->string('ip', 50)->nullable();
            $table->integer('port')->nullable();
            $table->string('community', 50)->nullable();
            $table->string('version', 10)->nullable();
            $table->string('vendor', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olts');
    }
};
