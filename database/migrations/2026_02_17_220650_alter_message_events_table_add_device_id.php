<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_events', function (Blueprint $table) {

            $table->string('device_id')
                ->nullable()
                ->after('channel') // sesuaikan posisi kolom
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('message_events', function (Blueprint $table) {

            $table->dropIndex(['device_id']);
            $table->dropColumn('device_id');
        });
    }
};
