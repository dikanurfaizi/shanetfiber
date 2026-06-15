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
        // Kumpulan keyword baru seputar tagihan, pembayaran, dan rekening
        $newKeywords = [
            "info pembayaran",
            "info bayar",
            "pembayaran",
            "cara bayar",
            "cara pembayaran",
            "bayar tagihan",
            "link pembayaran",
            "link bayar",
            "mau bayar",
            "pembayaran wifi",
            "bayar wifi",
            "pembayaran internet",
            "info rekening",
            "rekening",
            "nomor rekening",
            "no rekening",
            "norek",
            "minta rekening",
            "transfer",
            "no transfer",
            "lewat transfer",
            "pilihan bank",
            "bank apa",
            "belum bayar",
            "rincian tagihan",
            "total tagihan",
            "berapa tagihan",
            "habis berapa",
            "nominal",
            "jumlah bayar",
            "tunggakan",
            "sabaraha tagihan",
            "nomer rekening",
            "info tagihan",
            "tagihan",
            "cek tagihan",
            "tagihan saya",
            "mau bayar"
        ];

        // Ambil data lama berdasarkan slug 'billing_info' (Silakan sesuaikan nama slug-nya)
        $row = DB::table('whatsapp_messages')->where('slug', 'info_tagihan')->first();

        if ($row) {
            // Decode data lama (jika null atau kosong, default ke array kosong)
            $existingKeywords = json_decode($row->keywords, true) ?? [];

            // Gabungkan kata lama dan baru, lalu hilangkan duplikat
            $mergedKeywords = array_unique(array_merge($existingKeywords, $newKeywords));

            // Reset index array
            $finalKeywords = array_values($mergedKeywords);

            // Update ke database
            DB::table('whatsapp_messages')
                ->where('slug', 'info_tagihan')
                ->update([
                    'keywords' => json_encode($finalKeywords),
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $newKeywords = [
            "info pembayaran",
            "info bayar",
            "pembayaran",
            "cara bayar",
            "cara pembayaran",
            "bayar tagihan",
            "link pembayaran",
            "link bayar",
            "mau bayar",
            "pembayaran wifi",
            "bayar wifi",
            "pembayaran internet",
            "info rekening",
            "rekening",
            "nomor rekening",
            "no rekening",
            "norek",
            "minta rekening",
            "transfer",
            "no transfer",
            "lewat transfer",
            "pilihan bank",
            "bank apa",
            "belum bayar",
            "rincian tagihan",
            "total tagihan",
            "berapa tagihan",
            "habis berapa",
            "nominal",
            "jumlah bayar",
            "tunggakan",
            "sabaraha tagihan",
            "nomer rekening"
        ];

        $row = DB::table('whatsapp_messages')->where('slug', 'info_tagihan')->first();

        if ($row) {
            $existingKeywords = json_decode($row->keywords, true) ?? [];

            // Hapus keyword baru dari array lama (proses rollback)
            $filteredKeywords = array_diff($existingKeywords, $newKeywords);

            DB::table('whatsapp_messages')
                ->where('slug', 'info_tagihan')
                ->update([
                    'keywords' => json_encode(array_values($filteredKeywords)),
                    'updated_at' => now()
                ]);
        }
    }
};
