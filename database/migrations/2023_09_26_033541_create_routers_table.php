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
        if (!Schema::hasTable('merk_routers')) {
            Schema::create('merk_routers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('disabled', ['false', 'true'])->default('false');
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('type_routers')) {
            Schema::create('type_routers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('merk_router_id')->references('id')->on('merk_routers')->onDelete('cascade');
                $table->string('name');
                $table->enum('disabled', ['false', 'true'])->default('false');
                $table->enum('server_type', ['false', 'true'])->default('false');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merk_onts', 'type_onts');
    }
};
