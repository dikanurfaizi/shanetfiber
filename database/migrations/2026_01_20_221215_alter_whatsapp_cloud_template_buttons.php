<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('whatsapp_cloud_template_buttons', function (Blueprint $table) {

            // rename enum to support more types safely
            $table->string('type', 30)->change();

            // new polymorphic fields
            $table->string('phone_number', 50)->nullable()->after('url');
            $table->string('payload', 255)->nullable()->after('phone_number');
            $table->string('code', 255)->nullable()->after('payload');

            // index refinement
            $table->index(['type', 'position']);
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_cloud_template_buttons', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'payload', 'code']);
        });
    }
};
