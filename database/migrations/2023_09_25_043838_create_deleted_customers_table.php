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
        if (!Schema::hasTable('deleted_customers')) {
            Schema::create('deleted_customers', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('slug')->nullable();
                $table->string('password')->nullable();
                $table->string('email')->nullable();
                $table->string('paket_id')->nullable();
                $table->string('mikrotik_id')->nullable();
                $table->string('secret_id')->nullable();
                $table->string('username')->nullable();
                $table->string('password_ppp')->nullable();
                $table->string('ppp_type_id')->nullable();
                $table->text('address')->nullable();
                $table->text('contact')->nullable();
                $table->string('activation_date')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_customers');
    }
};
