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
        Schema::table('billings', function (Blueprint $table) {
            // Drop kolom tax
            if (Schema::hasColumn('billings', 'tax')) {
                $table->dropColumn('tax');
            }

            // Drop foreign keys
            $table->dropForeign('billings_billing_subscription_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            // Tambah lagi kolom tax
            $table->decimal('tax', 12, 2)->unsigned()->default(0.00);

            // Balikin FK
            $table->foreign('billing_subscription_id')
                ->references('id')
                ->on('billing_subscription_periodes')
                ->onDelete('cascade');
        });
    }
};
