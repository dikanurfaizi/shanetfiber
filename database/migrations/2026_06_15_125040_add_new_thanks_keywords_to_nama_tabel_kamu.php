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
        // 1. Definisikan keyword tambahan baru
        $newKeywords = [
            "nuhun pisan",
            "hatur nuhun pisan",
            "nuhun sateuacana",
            "hatur nuhun sateuacanna",
            "nuhun loba",
            "hatur nuhun gusti",
            "sami-sami",
            "sawangsulna",
            "terima kasih banyak",
            "terima kasih banyak ya",
            "terima kasih ya",
            "makasih bgt",
            "makasih banget",
            "makasih banyakk",
            "makasii",
            "makasiih",
            "makacih",
            "makasiya",
            "makasya",
            "tengkiu",
            "tengkyuu",
            "tengks",
            "thnk u",
            "thanku",
            "thankyu",
            "tyvm",
            "trims ya",
            "trimss"
        ];

        // 2. Ambil data lama berdasarkan slug 'thank_you'
        $row = DB::table('whatsapp_messages')->where('slug', 'thank_you')->first();

        if ($row) {
            // Decode data lama (jika null atau kosong, default ke array kosong)
            $existingKeywords = json_decode($row->keywords, true) ?? [];

            // Gabungkan kata lama dan baru, lalu hilangkan duplikat menggunakan array_unique
            $mergedKeywords = array_unique(array_merge($existingKeywords, $newKeywords));

            // Reset index array agar urut kembali (0, 1, 2, dst) sebelum di-json_encode
            $finalKeywords = array_values($mergedKeywords);

            // Update kembali ke database
            DB::table('whatsapp_messages')
                ->where('slug', 'thank_you')
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
            "nuhun pisan",
            "hatur nuhun pisan",
            "nuhun sateuacana",
            "hatur nuhun sateuacanna",
            "nuhun loba",
            "hatur nuhun gusti",
            "sami-sami",
            "sawangsulna",
            "terima kasih banyak",
            "terima kasih banyak ya",
            "terima kasih ya",
            "makasih bgt",
            "makasih banget",
            "makasih banyakk",
            "makasii",
            "makasiih",
            "makacih",
            "makasiya",
            "makasya",
            "tengkiu",
            "tengkyuu",
            "tengks",
            "thnk u",
            "thanku",
            "thankyu",
            "tyvm",
            "trims ya",
            "trimss"
        ];

        $row = DB::table('whatsapp_messages')->where('slug', 'thank_you')->first();

        if ($row) {
            $existingKeywords = json_decode($row->keywords, true) ?? [];

            // Hapus keyword baru dari array lama (proses rollback)
            $filteredKeywords = array_diff($existingKeywords, $newKeywords);

            DB::table('whatsapp_messages')
                ->where('slug', 'thank_you')
                ->update([
                    'keywords' => json_encode(array_values($filteredKeywords)),
                    'updated_at' => now()
                ]);
        }
    }
};
