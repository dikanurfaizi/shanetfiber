<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_cloud_template_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('template_id')
                ->constrained('whatsapp_cloud_templates')
                ->cascadeOnDelete();

            $table->enum('type', ['HEADER', 'BODY', 'FOOTER', 'BUTTONS', 'UNKNOWN']);
            $table->string('format', 30)->nullable(); // TEXT, IMAGE, DOCUMENT
            $table->longText('text')->nullable();
            $table->integer('position')->default(0);

            $table->json('raw_component');

            $table->timestamps();

            $table->index(['template_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_cloud_template_components');
    }
};
