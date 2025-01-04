<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVlanIdToCustomerOnts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_onts', function (Blueprint $table) {
            $table->integer('vlan_id')->nullable()->after('merk_ont'); // Menggunakan tipe data INTEGER
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_onts', function (Blueprint $table) {
            $table->dropColumn('vlan_id'); // Menghapus kolom vlan_id
        });
    }
}
