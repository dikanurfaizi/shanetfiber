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
            $table->string('jid')
                ->nullable() // sementara nullable untuk safe deployment
                ->after('number');

            // optional: enforce unique kalau 1 device hanya boleh 1 nomor
            $table->unique('jid');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_numbers', function (Blueprint $table) {
            $table->dropUnique(['jid']);
            $table->dropColumn('jid');
        });
    }
};
