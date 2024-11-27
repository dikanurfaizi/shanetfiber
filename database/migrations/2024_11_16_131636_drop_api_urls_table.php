<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DropApiUrlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Hapus data terlebih dahulu jika ada
        DB::table('api_urls')->delete();

        // Hapus tabel api_urls
        Schema::dropIfExists('api_urls');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Tidak ada tindakan pembalikan karena kita tidak membuat ulang tabel api_urls
        // pada migrasi ini, hanya menghapusnya
    }
}
