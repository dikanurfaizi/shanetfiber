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
        Schema::create('onus', function (Blueprint $table) {
            $table->id(); // AUTO_INCREMENT primary key

            $table->unsignedBigInteger('olt_id')->nullable(); // foreign key ke olts.id
            $table->string('name', 255)->nullable();
            $table->string('tx_power', 20)->nullable();
            $table->string('rx_power', 20)->nullable();
            $table->enum('status', ['Up', 'Down'])->default('Down');
            $table->dateTime('last_updated')->nullable();
            $table->string('mac_address', 100)->nullable();
            $table->string('temperature', 100)->nullable();

            // Index
            $table->index('olt_id');

            // Foreign key
            $table->foreign('olt_id')
                ->references('id')->on('olts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onus');
    }
};
