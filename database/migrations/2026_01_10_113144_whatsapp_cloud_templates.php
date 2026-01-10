<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_cloud_templates', function (Blueprint $table) {
            $table->id();

            $table->string('waba_template_id', 64)->index();
            $table->unsignedBigInteger('account_id')->index();

            $table->string('name', 150)->index();
            $table->string('language', 10)->default('id');
            $table->string('category', 50)->nullable();
            $table->string('sub_category', 50)->nullable();
            $table->string('status', 20)->nullable();
            $table->string('parameter_format', 20)->nullable();

            // Canonical Meta payload
            $table->json('raw_payload');

            $table->timestamps();

            $table->unique(['account_id', 'name', 'language'], 'wa_tpl_unique_name_lang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_cloud_templates');
    }
};
