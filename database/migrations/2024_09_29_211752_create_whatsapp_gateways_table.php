<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappGatewaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_gateways', function (Blueprint $table) {
            $table->id(); // Automatically creates an auto-incrementing ID column
            $table->string('name'); // For the WhatsApp Gateway name
            $table->enum('status', ['active', 'inactive']); // Status can be either active or inactive
            $table->string('description', 500); // Description of the WhatsApp Gateway
            $table->integer('schedule_date')->default(1); // Schedule day, typically will be 1
            $table->string('schedule_time', 5); // Store time as 'HH:mm'
            $table->string('message', 500); // The actual message to be sent via WhatsApp
            $table->timestamps(); // Optional: Created_at and updated_at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_gateways');
    }
}
