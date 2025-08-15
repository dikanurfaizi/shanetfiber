<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Tambah kolom customer_name ke tabel billings
        Schema::table('billings', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('customer_id');
            $table->string('customer_address')->nullable()->after('customer_name');
        });

        // Update kolom customer_name berdasarkan relasi ke tabel customers
        DB::statement("
            UPDATE billings b
            JOIN customers c ON b.customer_id = c.id
            SET b.customer_name = c.name
            WHERE b.customer_id IS NOT NULL
        ");

        DB::statement("
            UPDATE billings b
            JOIN customers c ON b.customer_id = c.id
            SET b.customer_address = c.address
            WHERE b.customer_id IS NOT NULL
        ");
    }

    public function down()
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn('customer_name');
            $table->dropColumn('customer_address');
        });
    }
};
