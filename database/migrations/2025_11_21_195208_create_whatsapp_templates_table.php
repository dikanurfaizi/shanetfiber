<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();

            $table->string('meta_waba_id');                         // WABA ID
            $table->string('meta_id');                      // ID dari Meta
            $table->string('name');                                   // nama template
            $table->string('language', 30);                           // id, en_US, dsb.
            $table->string('category', 50);                           // MARKETING / UTILITY
            $table->string('sub_category', 50)->nullable();           // CUSTOM
            $table->string('parameter_format', 30)->default('POSITIONAL');
            $table->string('status', 30);                             // APPROVED, PENDING

            $table->json('components');                               // full komponen JSON
            $table->json('examples')->nullable();                     // example json

            $table->integer('placeholders_count')->default(0);

            $table->boolean('has_header')->default(false);
            $table->boolean('has_body')->default(false);
            $table->boolean('has_footer')->default(false);
            $table->boolean('has_buttons')->default(false);

            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            $table->unique(['meta_waba_id', 'meta_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
