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
        Schema::create('olt_update_logs', function (Blueprint $table) {
            $table->id(); // AUTO_INCREMENT primary key
            $table->dateTime('updated_at')->nullable();
            $table->integer('success_count')->nullable();
            $table->integer('failed_count')->nullable();
            $table->text('details')->nullable();
            $table->unsignedBigInteger('olt_id')->nullable();
            $table->integer('onu_count')->nullable();

            // kalau nanti perlu relasi ke olts, tambahkan FK
            // $table->foreign('olt_id')
            //       ->references('id')->on('olts')
            //       ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olt_update_logs');
    }
};
