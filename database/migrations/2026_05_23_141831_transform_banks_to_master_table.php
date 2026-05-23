<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. NONAKTIFKAN FOREIGN KEY CHECKS SEMENTARA AGAR PROSES AMAN
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. KOSONGKAN TABEL PIVOT SEMENTARA
        DB::table('bank_users')->truncate();

        // 🔥 PERBAIKAN UTAMA: KOSONGKAN TABEL BANKS DI AWAL SEBELUM UBAH STRUKTUR
        // Ini memastikan tidak ada data duplikat tersisa saat index UNIQUE dipasang
        DB::table('banks')->truncate();

        // 3. UBAH STRUKTUR TABEL BANKS (Aman dilakukan karena tabel sudah kosong total)
        Schema::table('banks', function (Blueprint $table) {
            if (Schema::hasColumn('banks', 'user_id')) {
                $table->dropForeign('banks_user_id_foreign');
                $table->dropColumn('user_id');
            }

            $table->unique('account_number', 'banks_account_number_unique');
            $table->unique('slug', 'banks_slug_unique');
        });

        // Aktifkan kembali pengecekan foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 4. INSERT 3 DATA MASTER REKENING PUSAT
        $currentTimestamp = now();

        DB::table('banks')->insert([
            [
                'id'             => 1,
                'slug'           => 'bank-central-asia-pusat',
                'name'           => 'Dian Ardiansyah',
                'bank'           => 'BCA',
                'account_number' => '2990407315',
                'disabled'       => 'false',
                'created_at'     => $currentTimestamp,
                'updated_at'     => $currentTimestamp,
            ],
            [
                'id'             => 2,
                'slug'           => 'bank-mandiri-pusat',
                'name'           => 'Dian Ardiansyah',
                'bank'           => 'MANDIRI',
                'account_number' => '1730021468690',
                'disabled'       => 'false',
                'created_at'     => $currentTimestamp,
                'updated_at'     => $currentTimestamp,
            ],
            [
                'id'             => 3,
                'slug'           => 'ewallet-dana-pusat',
                'name'           => 'Dian Ardiansyah',
                'bank'           => 'DANA',
                'account_number' => '085795909904',
                'disabled'       => 'false',
                'created_at'     => $currentTimestamp,
                'updated_at'     => $currentTimestamp,
            ],
        ]);

        // 5. INSERT KE TABEL BANK_USERS SESUAI MAPPING YANG BENAR
        DB::statement("
            INSERT INTO bank_users (user_id, bank_id, created_at, updated_at) VALUES
            (1, 1, ?, ?), (1, 2, ?, ?), (1, 3, ?, ?),
            (2, 2, ?, ?), (2, 3, ?, ?),
            (5, 2, ?, ?), (5, 3, ?, ?),
            (6, 1, ?, ?), (6, 2, ?, ?), (6, 3, ?, ?)
        ", [
            // User 1
            $currentTimestamp,
            $currentTimestamp,
            $currentTimestamp,
            $currentTimestamp,
            $currentTimestamp,
            $currentTimestamp,
            // User 2
            $currentTimestamp,
            $currentTimestamp,
            $currentTimestamp,
            $currentTimestamp,
            // User 5
            $currentTimestamp,
            $currentTimestamp,
            $currentTimestamp,
            $currentTimestamp,
            // User 6
            $currentTimestamp,
            $currentTimestamp,
            $currentTimestamp,
            $currentTimestamp,
            $currentTimestamp,
            $currentTimestamp,
        ]);
    }

    public function down(): void
    {
        // 1. NONAKTIFKAN FOREIGN KEY CHECKS SEMENTARA
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. KOSONGKAN TABEL PIVOT DAN TABEL BANKS SEBELUM DI-RESTORASI
        DB::table('bank_users')->truncate();
        DB::table('banks')->truncate();

        // 3. KEMBALIKAN STRUKTUR TABEL BANKS SEPERTI SEMULA
        Schema::table('banks', function (Blueprint $table) {
            $table->dropUnique('banks_account_number_unique');
            $table->dropUnique('banks_slug_unique');

            if (!Schema::hasColumn('banks', 'user_id')) {
                $table->bigInteger('user_id')->unsigned()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 4. RESTORASI DATA ASLI KE TABEL BANKS SEBELUM DIUBAH
        DB::table('banks')->insert([
            [
                'user_id' => 1,
                'slug' => 'bank-central-asia-mhtv',
                'name' => 'Dian Ardiansyah',
                'bank' => 'BCA',
                'account_number' => '2990407315',
                'disabled' => 'false',
                'created_at' => '2025-01-02 22:59:37',
                'updated_at' => '2025-04-17 06:27:58',
            ],
            [
                'user_id' => 1,
                'slug' => 'bank-mandiri-lagt',
                'name' => 'Dian Ardiansyah',
                'bank' => 'Mandiri',
                'account_number' => '1730021468690',
                'disabled' => 'false',
                'created_at' => '2025-01-02 23:01:32',
                'updated_at' => '2026-05-18 16:09:32',
            ],
            [
                'user_id' => 1,
                'slug' => 'dana-dzmo',
                'name' => 'Dian Ardiansyah',
                'bank' => 'DANA',
                'account_number' => '085795909904',
                'disabled' => 'false',
                'created_at' => '2025-01-17 01:17:20',
                'updated_at' => '2025-04-17 06:27:58',
            ],
            [
                'user_id' => 2,
                'slug' => 'bank-mandiri-vkqs',
                'name' => 'Dian Ardiansyah',
                'bank' => 'Mandiri',
                'account_number' => '1730021468690',
                'disabled' => 'false',
                'created_at' => '2025-01-03 02:28:36',
                'updated_at' => '2025-04-17 06:27:58',
            ],
            [
                'user_id' => 2,
                'slug' => 'dana-exiv',
                'name' => 'Dian Ardiansyah',
                'bank' => 'DANA',
                'account_number' => '085795909904',
                'disabled' => 'false',
                'created_at' => '2025-01-14 17:34:08',
                'updated_at' => '2025-04-17 06:27:58',
            ],
            [
                'user_id' => 5,
                'slug' => 'mandiri-bjp5',
                'name' => 'Dian Ardiansyah',
                'bank' => 'Mandiri',
                'account_number' => '1730021468690',
                'disabled' => 'false',
                'created_at' => '2025-04-14 19:39:31',
                'updated_at' => '2025-04-17 06:27:58',
            ],
            [
                'user_id' => 5,
                'slug' => 'dana-y6is',
                'name' => 'Dian Ardiansyah',
                'bank' => 'DANA',
                'account_number' => '085795909904',
                'disabled' => 'false',
                'created_at' => '2025-04-15 17:39:47',
                'updated_at' => '2025-10-01 22:08:45',
            ],
            [
                'user_id' => 6,
                'slug' => 'mandiri-9dpl',
                'name' => 'Dian Ardiansyah',
                'bank' => 'Mandiri',
                'account_number' => '1730021468690',
                'disabled' => 'false',
                'created_at' => '2025-12-08 05:13:48',
                'updated_at' => '2025-12-11 21:27:47',
            ],
            [
                'user_id' => 6,
                'slug' => 'dana-qe1k',
                'name' => 'Dian Ardiansyah',
                'bank' => 'DANA',
                'account_number' => '085795909904',
                'disabled' => 'false',
                'created_at' => '2025-12-11 21:28:55',
                'updated_at' => '2025-12-11 21:29:03',
            ],
            [
                'user_id' => 6,
                'slug' => 'bca-vmq2',
                'name' => 'Dian Ardiansyah',
                'bank' => 'BCA',
                'account_number' => '2990407315',
                'disabled' => 'false',
                'created_at' => '2026-05-23 13:00:52',
                'updated_at' => '2026-05-23 13:01:07',
            ],
        ]);
    }
};
