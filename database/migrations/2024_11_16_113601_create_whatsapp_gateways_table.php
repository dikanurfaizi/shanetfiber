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
        if (Schema::hasTable('whatsapp_gateways')) {
            DB::table('whatsapp_gateways')->delete(); // Menghapus data dengan mematuhi foreign key
        }

        if (!Schema::hasTable('whatsapp_gateways')) {
            Schema::create('whatsapp_gateways', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->integer('remaining_day')->default(1);
                $table->integer('schedule_time')->default(10);
                $table->foreignId('whatsapp_number_id')->nullable()->references('id')->on('whatsapp_numbers')->onDelete('cascade');
                $table->integer('country_code')->default(62)->nullable();
                $table->string('description')->nullable();
                $table->string('url_callback')->nullable(true);
                $table->string('api_key')->nullable(true);
                $table->string('cert_file')->nullable(true);
                $table->boolean('disabled')->default(true);
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
        Schema::dropIfExists('whatsapp_gateways');
    }
};
