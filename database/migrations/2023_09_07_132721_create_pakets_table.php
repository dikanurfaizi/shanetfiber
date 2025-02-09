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
        if (!Schema::hasTable('pakets')) {
            Schema::create('pakets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mikrotik_id')->references('id')->on('mikrotiks')->onDelete('cascade');
                $table->string('name');
                $table->string('profile');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->integer('price');
                //Add new disable
                $table->enum('disabled', ['false', 'true'])->default('true');
                //end
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                // $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('password');
                $table->string('email')->unique()->nullable();
                $table->foreignId('paket_id')->references('id')->on('pakets')->onDelete('cascade')->nullable();
                $table->foreignId('mikrotik_id')->references('id')->on('mikrotiks')->onDelete('cascade');
                $table->string('secret_id')->nullable();
                $table->string('username')->nullable();
                $table->string('password_ppp')->nullable();
                $table->foreignId('ppp_type_id')->references('id')->on('ppp_types')->onDelete('cascade')->nullable();
                $table->string('simpleque_id')->nullable();
                $table->string('simpleque_name')->nullable();
                $table->string('ip_static')->nullable();
                $table->string('mac_address')->nullable();
                $table->string('interface')->nullable();
                $table->string('arp_id')->nullable();
                $table->string('address_list_id')->nullable();
                $table->text('address')->nullable();
                $table->text('contact')->nullable();
                //Add new disable
                $table->enum('disabled', ['false', 'true'])->default('true');
                $table->enum('activation', ['false', 'true'])->default('false');
                $table->string('activation_date')->nullable();
                //end
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('pakets');
    }
};
