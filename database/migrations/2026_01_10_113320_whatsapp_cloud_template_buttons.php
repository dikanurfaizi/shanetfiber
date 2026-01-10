<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_cloud_template_buttons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('component_id')
                ->constrained('whatsapp_cloud_template_components')
                ->cascadeOnDelete();

            $table->enum('type', ['URL', 'QUICK_REPLY', 'PHONE', 'UNKNOWN']);
            $table->string('text', 255)->nullable();
            $table->text('url')->nullable();
            $table->integer('position')->default(0);

            $table->json('raw_button');

            $table->timestamps();

            $table->index(['component_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_cloud_template_buttons');
    }
};
