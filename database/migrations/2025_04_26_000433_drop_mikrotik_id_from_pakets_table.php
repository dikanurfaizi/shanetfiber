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
        Schema::table('pakets', function (Blueprint $table) {
            // Drop foreign key constraint (pastikan nama sesuai, atau gunakan DB::statement jika perlu)
            $table->dropForeign(['mikrotik_id']);

            // Drop column after constraint is dropped
            $table->dropColumn('mikrotik_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pakets', function (Blueprint $table) {
            $table->unsignedBigInteger('mikrotik_id')->nullable();

            // Re-add foreign key (pastikan referencing table benar)
            $table->foreign('mikrotik_id')->references('id')->on('mikrotiks')->onDelete('set null');
        });
    }
};
