<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_gateway_accounts', function (Blueprint $table) {

            if (Schema::hasColumn('whatsapp_gateway_accounts', 'meta_waba_id')) {
                $table->dropColumn('meta_waba_id');
            }

            if (Schema::hasColumn('whatsapp_gateway_accounts', 'meta_version')) {
                $table->dropColumn('meta_version');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_gateway_accounts', function (Blueprint $table) {
            $table->string('meta_waba_id')->nullable()->after('whatsapp_gateway_id');
            $table->string('meta_version')->nullable()->after('meta_waba_id');
        });
    }
};
