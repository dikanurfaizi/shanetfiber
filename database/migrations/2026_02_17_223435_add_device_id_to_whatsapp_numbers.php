<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_numbers', function (Blueprint $table) {

            // device_id dari Gowa multi-device
            $table->string('device_id')
                ->nullable() // sementara nullable untuk safe deployment
                ->after('number');

            // optional: enforce unique kalau 1 device hanya boleh 1 nomor
            $table->unique('device_id');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_numbers', function (Blueprint $table) {
            $table->dropUnique(['device_id']);
            $table->dropColumn('device_id');
        });
    }
};
