<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_gateway_accounts', function (Blueprint $table) {
            $table->string('purpose', 50)
                  ->default('default')
                  ->after('meta_user_access_token');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_gateway_accounts', function (Blueprint $table) {
            $table->dropColumn('purpose');
        });
    }
};
