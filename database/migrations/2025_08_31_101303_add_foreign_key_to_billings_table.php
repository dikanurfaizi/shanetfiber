<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            // Cek apakah index dengan nama ini ada
            $indexExists = DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', 'billings')
                ->where('index_name', 'billings_billing_subscription_id_foreign')
                ->exists();

            if ($indexExists) {
                $table->dropIndex('billings_billing_subscription_id_foreign');
            }

            // Cek apakah foreign key dengan nama ini ada
            $fkExists = DB::table('information_schema.referential_constraints')
                ->where('constraint_schema', DB::getDatabaseName())
                ->where('constraint_name', 'billings_billing_subscription_id_foreign')
                ->exists();

            if ($fkExists) {
                $table->dropForeign('billings_billing_subscription_id_foreign');
            }

            // Tambahkan foreign key baru dengan cascade
            $table->foreign('billing_subscription_id', 'billings_billing_subscription_id_foreign')
                ->references('id')
                ->on('billing_subscription_periodes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $fkExists = DB::table('information_schema.referential_constraints')
                ->where('constraint_schema', DB::getDatabaseName())
                ->where('constraint_name', 'billings_billing_subscription_id_foreign')
                ->exists();

            if ($fkExists) {
                $table->dropForeign('billings_billing_subscription_id_foreign');
            }

            // balikin lagi ke index biasa
            $indexExists = DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', 'billings')
                ->where('index_name', 'billings_billing_subscription_id_foreign')
                ->exists();

            if (!$indexExists) {
                $table->index('billing_subscription_id', 'billings_billing_subscription_id_foreign');
            }
        });
    }
};
