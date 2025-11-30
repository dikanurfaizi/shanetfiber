<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_templates', 'phone_number_id')) {
                $table->string('phone_number_id', 50)
                    ->nullable()
                    ->after('meta_waba_id')
                    ->comment('Phone number ID used for sending this template');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_templates', 'phone_number_id')) {
                $table->dropColumn('phone_number_id');
            }
        });
    }
};
