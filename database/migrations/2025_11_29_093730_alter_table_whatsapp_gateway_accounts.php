<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * 0. DROP FK lama (jika ada) — WAJIB
         */
        Schema::table('whatsapp_gateway_accounts', function (Blueprint $table) {
            // Dapatkan seluruh foreign key pada table
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('whatsapp_gateway_accounts');

            // Jika kolom whatsapp_gateway_id punya foreign key, drop semua FK yang menunjuk ke kolom itu
            if ($doctrineTable->hasColumn('whatsapp_gateway_id')) {
                foreach ($doctrineTable->getForeignKeys() as $fk) {
                    if (in_array('whatsapp_gateway_id', $fk->getLocalColumns())) {
                        $table->dropForeign($fk->getName());
                    }
                }
            }
        });

        /**
         * 1. DROP kolom lama yang tidak dipakai
         */
        Schema::table('whatsapp_gateway_accounts', function (Blueprint $table) {

            $drop = [
                'meta_phone_number_id',
                'whatsapp_number_id',
                'webhook_client_header',
                'webhook_client_secret',
                'purpose',
                'description',
                'base_url',
                'webhook_secret',
                'basic_auth_username',
                'basic_auth_password',
                'auth_enabled',
                'cert_file',
                'meta_waba_id',
                'meta_graph_version',
                'meta_graph_url',
                'is_active',
                'whatsapp_gateway_id',
            ];

            foreach ($drop as $col) {
                if (Schema::hasColumn('whatsapp_gateway_accounts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        /**
         * 2. Tambah kolom whatsapp_gateway_id TANPA FK dulu
         */
        Schema::table('whatsapp_gateway_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'whatsapp_gateway_id')) {
                $table->unsignedBigInteger('whatsapp_gateway_id')->nullable()->after('id');
            }
        });

        /**
         * 3. Pastikan whatsapp_gateways memiliki row default
         */
        $gowaId = DB::table('whatsapp_gateways')->where('slug', 'gowa')->value('id');
        $metaId = DB::table('whatsapp_gateways')->where('slug', 'meta_cloud')->value('id');

        if (! $gowaId) {
            $gowaId = DB::table('whatsapp_gateways')->insertGetId([
                'slug' => 'gowa',
                'name' => 'Gowa On-Prem',
                'type' => 'onprem',
                'is_active' => false,
                'description' => 'Gowa WhatsApp On-Premise Gateway',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! $metaId) {
            $metaId = DB::table('whatsapp_gateways')->insertGetId([
                'slug' => 'meta_cloud',
                'name' => 'Meta Cloud API',
                'type' => 'cloud',
                'is_active' => false,
                'description' => 'Meta (Facebook) Official Cloud API Gateway',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        /**
         * 4. PERBAIKI SEMUA orphan rows!
         *    UPDATE di LUAR Schema::table — inilah kunci solusinya
         */
        DB::table('whatsapp_gateway_accounts')
            ->whereNull('whatsapp_gateway_id')
            ->orWhereNotIn('whatsapp_gateway_id', [$gowaId])
            ->update(['whatsapp_gateway_id' => $gowaId]);

        /**
         * 5. Sekarang aman untuk menambahkan FK
         */
        Schema::table('whatsapp_gateway_accounts', function (Blueprint $table) {
            $table->foreign('whatsapp_gateway_id')
                ->references('id')
                ->on('whatsapp_gateways')
                ->onDelete('cascade');
        });

        /**
         * 6. Tambah kolom baru
         */
        Schema::table('whatsapp_gateway_accounts', function (Blueprint $table) {

            // general fields
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'name')) {
                $table->string('name')->nullable()->after('whatsapp_gateway_id');
            }
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'purpose')) {
                $table->string('purpose')->nullable()->after('name');
            }
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'description')) {
                $table->text('description')->nullable()->after('purpose');
            }
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'base_url')) {
                $table->string('base_url')->nullable()->after('description');
            }
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'webhook_secret')) {
                $table->string('webhook_secret')->nullable()->after('base_url');
            }
            // Gowa
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'basic_auth_username')) {
                $table->string('basic_auth_username')->nullable()->after('webhook_secret');
            }
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'basic_auth_password')) {
                $table->string('basic_auth_password')->nullable()->after('basic_auth_username');
            }
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'auth_enabled')) {
                $table->boolean('auth_enabled')->default(false)->after('basic_auth_password');
            }
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'cert_file')) {
                $table->string('cert_file')->nullable()->after('auth_enabled');
            }
            // Meta
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'meta_waba_id')) {
                $table->string('meta_waba_id')->nullable()->after('cert_file');
            }
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'meta_graph_version')) {
                $table->string('meta_graph_version')->nullable()->default('v22.0')->after('meta_waba_id');
            }
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'meta_graph_url')) {
                $table->string('meta_graph_url')->nullable()->after('meta_graph_version');
            }
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'meta_user_access_token')) {
                $table->text('meta_user_access_token')->nullable()->after('meta_graph_url');
            }
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'meta_webhook_verify_token')) {
                $table->string('meta_webhook_verify_token')->nullable()->after('meta_user_access_token');
            }
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'meta_webhook_app_secret')) {
                $table->string('meta_webhook_app_secret')->nullable()->after('meta_webhook_verify_token');
            }
            if (! Schema::hasColumn('whatsapp_gateway_accounts', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('meta_webhook_app_secret');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_gateway_accounts', function (Blueprint $table) {
            try {
                $table->dropForeign(['whatsapp_gateway_id']);
            } catch (\Throwable $e) {
            }

            $cols = [
                'meta_webhook_app_secret',
                'meta_webhook_verify_token',
                'meta_user_access_token',
                'meta_graph_url',
                'meta_graph_version',
                'meta_waba_id',
                'cert_file',
                'auth_enabled',
                'basic_auth_password',
                'basic_auth_username',
                'webhook_secret',
                'base_url',
                'description',
                'purpose',
                'name',
                'is_active',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('whatsapp_gateway_accounts', $col)) {
                    $table->dropColumn($col);
                }
            }

            if (Schema::hasColumn('whatsapp_gateway_accounts', 'whatsapp_gateway_id')) {
                $table->dropColumn('whatsapp_gateway_id');
            }
        });
    }
};
