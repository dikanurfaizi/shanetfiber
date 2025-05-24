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
        Schema::table('websystems', function (Blueprint $table) {
            $table->boolean('billing_auto_generate')->default(false)->after('long_customer_code'); // ubah 'some_column' sesuai posisi
            $table->unsignedTinyInteger('billing_auto_day')->nullable()->after('billing_auto_generate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websystems', function (Blueprint $table) {
            $table->dropColumn(['billing_auto_generate', 'billing_auto_day']);
        });
    }
};
