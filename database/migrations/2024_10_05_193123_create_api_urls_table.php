<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateApiUrlsTable extends Migration
{
    public function up()
    {
        Schema::create('api_urls', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url')->unique(); // Menyimpan URL
            $table->timestamps(); // Menyimpan waktu pembuatan dan pembaruan
        });

        DB::table('api_urls')->insert([
            [
                'id' => 1,
                'name' => 'send message',
                'url' => 'http://103.175.219.15:3000/send/message',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'group',
                'url' => 'http://103.175.219.15:3000/group',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('api_urls');
    }
}
