<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_gateway_accounts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('whatsapp_gateway_id');

            $table->string('meta_version', 50)->nullable();
            $table->string('meta_waba_id', 150)->nullable();
            $table->string('meta_phone_number_id', 150)->nullable();
            $table->text('meta_user_access_token')->nullable();

            $table->boolean('is_active')->default(false);

            $table->timestamps();

            $table->foreign('whatsapp_gateway_id')
                ->references('id')->on('whatsapp_gateways')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_gateway_accounts');
    }
};
