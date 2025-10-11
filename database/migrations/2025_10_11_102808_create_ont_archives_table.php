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
        Schema::create('ont_archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_olt_id')->index()->comment('Referensi OLT asal data ONT');
            $table->string('ont_index')->comment('Index ONT sesuai OID SNMP');
            $table->string('pon_group')->nullable()->comment('PON group / port label dari OLT');
            $table->string('name')->nullable()->comment('Nama ONT');
            $table->string('tx_power')->nullable()->comment('TX Power (dBm)');
            $table->string('rx_power')->nullable()->comment('RX Power (dBm)');
            $table->string('status')->nullable()->comment('Status koneksi ONT');
            $table->string('mac_address')->nullable()->comment('Alamat MAC ONT');
            $table->string('temperature')->nullable()->comment('Suhu ONT');
            $table->timestamp('archived_at')->useCurrent()->comment('Waktu data diarsipkan');

            // Index tambahan untuk pencarian cepat
            $table->index(['status']);
            $table->index(['pon_group']);

            // Opsional: relasi ke OLT jika tabel olts ada
            $table->foreign('source_olt_id')->references('id')->on('olts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ont_archives');
    }
};
