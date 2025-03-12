<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('whatsapp_gateways', function (Blueprint $table) {
            $table->string('basic_auth_username')->nullable()->after('disabled');
            $table->string('basic_auth_password')->nullable()->after('basic_auth_username');
            $table->enum('auth_disabled', ['true', 'false'])->default('false')->after('basic_auth_password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('whatsapp_gateways', function (Blueprint $table) {
            $table->dropColumn(['basic_auth_username', 'basic_auth_password']);
        });
    }
};
