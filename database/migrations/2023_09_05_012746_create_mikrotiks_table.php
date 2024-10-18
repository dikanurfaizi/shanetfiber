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

        if (!Schema::hasTable('mikrotiks')) {
            Schema::create('mikrotiks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug');
                $table->string('host');
                $table->string('username');
                $table->string('password');
                $table->bigInteger('port');
                //Add new
                $table->string('merk_router')->nullable();
                $table->string('type_router')->nullable();
                $table->enum('disabled', ['false', 'true'])->default('true');
                //end
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mikrotiks');
    }
};
