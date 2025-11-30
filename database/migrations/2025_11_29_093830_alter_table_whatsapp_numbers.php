<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_numbers', function (Blueprint $table) {

            /*
            |--------------------------------------------------
            | DROP OLD COLUMNS
            |--------------------------------------------------
            */
            $oldColumns = [
                'api_key',
                'cert_file',
                'basic_auth_username',
                'basic_auth_password',
                'auth_enabled',
                'meta_user_access_token',
                'meta_webhook_verify_token',
                'meta_webhook_app_secret',
                'whatsapp_gateway_id',
                'disabled',
                'default',
            ];

            foreach ($oldColumns as $col) {
                if (Schema::hasColumn('whatsapp_numbers', $col)) {
                    $table->dropColumn($col);
                }
            }

            /*
            |--------------------------------------------------
            | ADD FOREIGN KEY TO ACCOUNT
            |--------------------------------------------------
            */
            if (! Schema::hasColumn('whatsapp_numbers', 'whatsapp_gateway_account_id')) {
                $table->unsignedBigInteger('whatsapp_gateway_account_id')
                    ->nullable()
                    ->after('id');

                $table->foreign('whatsapp_gateway_account_id')
                    ->references('id')
                    ->on('whatsapp_gateway_accounts')
                    ->onDelete('cascade');
            }

            /*
            |--------------------------------------------------
            | ADD STRUCTURE FIELDS
            |--------------------------------------------------
            */
            if (! Schema::hasColumn('whatsapp_numbers', 'slug')) {
                $table->string('slug')->nullable()->after('whatsapp_gateway_account_id');
            }

            if (! Schema::hasColumn('whatsapp_numbers', 'name')) {
                $table->string('name')->nullable()->after('slug');
            }

            if (! Schema::hasColumn('whatsapp_numbers', 'country_code')) {
                $table->string('country_code', 5)->nullable()->after('name');
            }

            if (! Schema::hasColumn('whatsapp_numbers', 'meta_phone_number_id')) {
                $table->string('meta_phone_number_id')->nullable()->after('country_code');
            }

            if (! Schema::hasColumn('whatsapp_numbers', 'webhook_client_header')) {
                $table->string('webhook_client_header')->nullable()->after('meta_phone_number_id');
            }

            if (! Schema::hasColumn('whatsapp_numbers', 'webhook_client_secret')) {
                $table->string('webhook_client_secret')->nullable()->after('webhook_client_header');
            }

            if (! Schema::hasColumn('whatsapp_numbers', 'description')) {
                $table->text('description')->nullable()->after('webhook_client_secret');
            }

            if (! Schema::hasColumn('whatsapp_numbers', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('description');
            }

            if (! Schema::hasColumn('whatsapp_numbers', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('is_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_numbers', function (Blueprint $table) {

            /*
            |--------------------------------------------------
            | DROP NEW COLUMNS
            |--------------------------------------------------
            */
            $newColumns = [
                'is_default',
                'is_enabled',
                'description',
                'webhook_client_secret',
                'webhook_client_header',
                'meta_phone_number_id',
                'country_code',
                'name',
                'slug',
            ];

            foreach ($newColumns as $col) {
                if (Schema::hasColumn('whatsapp_numbers', $col)) {
                    $table->dropColumn($col);
                }
            }

            /*
            |--------------------------------------------------
            | DROP FK COLUMN
            |--------------------------------------------------
            */
            if (Schema::hasColumn('whatsapp_numbers', 'whatsapp_gateway_account_id')) {
                $table->dropForeign(['whatsapp_gateway_account_id']);
                $table->dropColumn('whatsapp_gateway_account_id');
            }

            /*
            |--------------------------------------------------
            | RESTORE OLD COLUMNS
            |--------------------------------------------------
            */
            if (! Schema::hasColumn('whatsapp_numbers', 'disabled')) {
                $table->boolean('disabled')->default(false);
            }

            if (! Schema::hasColumn('whatsapp_numbers', 'default')) {
                $table->boolean('default')->default(false);
            }
        });
    }
};
