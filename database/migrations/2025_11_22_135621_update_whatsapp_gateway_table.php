<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_gateways', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_gateways', 'meta_waba_id')) {
                $table->string('meta_waba_id')->nullable()->after('auth_enabled');
            }

            if (!Schema::hasColumn('whatsapp_gateways', 'meta_version')) {
                $table->string('meta_version')->default('v22.0')->after('meta_waba_id');
            }

            if (!Schema::hasColumn('whatsapp_gateways', 'meta_graph_url')) {
                $table->string('meta_graph_url')
                    ->default('https://graph.facebook.com')
                    ->after('meta_version');
            }

            if(Schema::hasColumn('whatsapp_gateways', 'meta_base_url')) {
                $table->dropColumn('meta_base_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_gateways', function (Blueprint $table) {
            $table->dropColumn(['meta_waba_id', 'meta_version', 'meta_graph_url']);
        });
    }
};
