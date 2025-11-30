<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('whatsapp_gateways')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::statement('ALTER TABLE whatsapp_gateways AUTO_INCREMENT = 1;');

        Schema::table('whatsapp_gateways', function (Blueprint $table) {
            // Drop old FK + column
            if (Schema::hasColumn('whatsapp_gateways', 'whatsapp_number_id')) {
                try {
                    $table->dropForeign('whatsapp_gateways_whatsapp_number_id_foreign');
                } catch (\Exception $e) {
                }

                $table->dropColumn('whatsapp_number_id');
            }

            $dropColumns = [
                'remaining_day',
                'schedule_time',
                'country_code',
                'url_callback',
                'api_key',
                'cert_file',
                'disabled',
                'basic_auth_username',
                'basic_auth_password',
                'auth_enabled',
                'meta_waba_id',
                'meta_version',
                'meta_graph_url',
            ];

            foreach ($dropColumns as $col) {
                if (Schema::hasColumn('whatsapp_gateways', $col)) {
                    $table->dropColumn($col);
                }
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'type')) {
                $table->enum('type', ['onprem', 'cloud'])->nullable()->after('name');
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'description')) {
                $table->text('description')->nullable()->after('type');
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_gateways', function (Blueprint $table) {
            // Rollback: drop columns added
            if (Schema::hasColumn('whatsapp_gateways', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('whatsapp_gateways', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('whatsapp_gateways', 'type')) {
                $table->dropColumn('type');
            }

            // DOWN: Recreate columns but nullable
            if (! Schema::hasColumn('whatsapp_gateways', 'remaining_day')) {
                $table->integer('remaining_day')->nullable();
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'schedule_time')) {
                $table->time('schedule_time')->nullable();
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'country_code')) {
                $table->string('country_code')->nullable();
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'url_callback')) {
                $table->string('url_callback')->nullable();
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'api_key')) {
                $table->string('api_key')->nullable();
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'cert_file')) {
                $table->string('cert_file')->nullable();
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'disabled')) {
                $table->boolean('disabled')->default(false);
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'basic_auth_username')) {
                $table->string('basic_auth_username')->nullable();
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'basic_auth_password')) {
                $table->string('basic_auth_password')->nullable();
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'auth_enabled')) {
                $table->boolean('auth_enabled')->default(false);
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'meta_waba_id')) {
                $table->string('meta_waba_id')->nullable();
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'meta_version')) {
                $table->string('meta_version')->nullable();
            }

            if (! Schema::hasColumn('whatsapp_gateways', 'meta_graph_url')) {
                $table->string('meta_graph_url')->nullable();
            }
        });
    }
};
