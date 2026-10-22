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
        Schema::create('customer_provisioning_states', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel customers
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            // Flag status keberadaan
            $table->boolean('in_radius')->default(false);
            $table->boolean('in_mikrotik')->default(false);

            // Detail status sinkronisasi
            $table->enum('radius_status', ['pending', 'synced', 'failed', 'out_of_sync'])
                ->default('pending');
            $table->enum('mikrotik_status', ['pending', 'synced', 'failed', 'out_of_sync'])
                ->default('pending');

            // Timestamp waktu terakhir sinkronisasi
            $table->timestamp('last_radius_sync_at')->nullable();
            $table->timestamp('last_mikrotik_sync_at')->nullable();

            // Catatan error jika sinkronisasi gagal
            $table->text('last_error_message')->nullable();

            $table->timestamps();

            // Indexing untuk performa pencarian filter di UI
            $table->index(['in_radius', 'in_mikrotik']);
            $table->index('radius_status');
            $table->index('mikrotik_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_provisioning_states');
    }
};
