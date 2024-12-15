<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('whatsapp_numbers')) {
            DB::table('whatsapp_numbers')->delete(); // Menghapus data dengan mematuhi foreign key
        }

        if (!Schema::hasTable('whatsapp_numbers')) {
            Schema::create('whatsapp_numbers', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->string('number');
                $table->string('webhook_client_header')->nullable(true);
                $table->string('webhook_client_secret')->nullable(true);
                $table->string('description')->nullable();
                $table->boolean('disabled')->default(false);
                $table->boolean('default')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_numbers');
    }
};
