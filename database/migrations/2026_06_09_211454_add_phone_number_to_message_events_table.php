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
        // 1. Tambah kolom phone_number terlebih dahulu
        Schema::table('message_events', function (Blueprint $table) {
            $table->string('phone_number', 20)->nullable()->after('message_type');
        });

        // 2. Ambil data lama dan migrasikan nomor telepon dari request_payload
        // Menggunakan chunk untuk menghindari memori penuh jika datanya banyak
        DB::table('message_events')->orderBy('id')->chunk(100, function ($events) {
            foreach ($events as $event) {
                if (empty($event->request_payload)) {
                    continue;
                }

                // Decode JSON dari request_payload
                $payload = json_decode($event->request_payload, true);

                if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
                    continue;
                }

                // Cari key 'phone' atau 'to'
                $rawPhone = $payload['phone'] ?? $payload['to'] ?? null;

                if ($rawPhone) {
                    // Bersihkan jika ada format suffix WhatsApp seperti '@s.whatsapp.net'
                    $phoneNumber = explode('@', $rawPhone)[0];

                    // Batasi maksimal 20 karakter agar tidak sesuai panjang kolom
                    $phoneNumber = substr(trim($phoneNumber), 0, 20);

                    // Update data ke database
                    DB::table('message_events')
                        ->where('id', $event->id)
                        ->update(['phone_number' => $phoneNumber]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_events', function (Blueprint $table) {
            $table->dropColumn('phone_number');
        });
    }
};
