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
        Schema::table('billing_subscription_periodes', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at'); // tambahkan deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_subscription_periodes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
