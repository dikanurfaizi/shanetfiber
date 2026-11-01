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
        Schema::create('customer_location_histories', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel customers
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->onDelete('cascade');

            // Data Koordinat
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            // Catatan Alamat saat perubahan koordinat (opsional tapi berguna untuk audit)
            $table->text('address')->nullable();

            // Mengingat user/admin mana yang mengubah lokasi (opsional)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_location_histories');
    }
};
