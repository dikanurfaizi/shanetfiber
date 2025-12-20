<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('billing_payments', function (Blueprint $table) {
            // drop FK lama
            $table->dropForeign(['billing_id']);

            // buat FK baru dengan cascade
            $table->foreign('billing_id')
                ->references('id')
                ->on('billings')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->dropForeign(['billing_id']);

            $table->foreign('billing_id')
                ->references('id')
                ->on('billings')
                ->onDelete('restrict');
        });
    }
};
