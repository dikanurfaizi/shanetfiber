<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. NONAKTIFKAN FOREIGN KEY CHECKS SEMENTARA
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. UBAH STRUKTUR TABEL BANKS (Aman dengan pengecekan kolom)
        Schema::table('banks', function (Blueprint $table) {
            if (Schema::hasColumn('banks', 'user_id')) {
                try {
                    $table->dropForeign('banks_user_id_foreign');
                } catch (\Throwable $e) {
                }
                $table->dropColumn('user_id');
            }

            try {
                $table->unique('account_number', 'banks_account_number_unique');
            } catch (\Throwable $e) {
            }

            try {
                $table->unique('slug', 'banks_slug_unique');
            } catch (\Throwable $e) {
            }
        });

        // Aktifkan kembali pengecekan foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 3. INSERT MASTER BANKS (Gunakan insertOrIgnore agar aman dari duplikasi)
        $currentTimestamp = now();

        if (Schema::hasTable('banks')) {
            DB::table('banks')->insertOrIgnore([
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
        }

        // 4. INSERT BANK_USERS SECARA DINAMIS (Hanya jika user-nya sudah ada di database)
        if (Schema::hasTable('bank_users') && Schema::hasTable('users')) {
            $mappings = [
                ['user_id' => 1, 'bank_id' => 1],
                ['user_id' => 1, 'bank_id' => 2],
                ['user_id' => 1, 'bank_id' => 3],
                ['user_id' => 2, 'bank_id' => 2],
                ['user_id' => 2, 'bank_id' => 3],
                ['user_id' => 5, 'bank_id' => 2],
                ['user_id' => 5, 'bank_id' => 3],
                ['user_id' => 6, 'bank_id' => 1],
                ['user_id' => 6, 'bank_id' => 2],
                ['user_id' => 6, 'bank_id' => 3],
            ];

            foreach ($mappings as $mapping) {
                // Cek apakah user dengan ID tersebut benar-benar ada
                $userExists = DB::table('users')->where('id', $mapping['user_id'])->exists();

                // Cek juga apakah relasi pivot ini sudah pernah dimasukkan sebelumnya
                $pivotExists = DB::table('bank_users')
                    ->where('user_id', $mapping['user_id'])
                    ->where('bank_id', $mapping['bank_id'])
                    ->exists();

                if ($userExists && !$pivotExists) {
                    DB::table('bank_users')->insert([
                        'user_id'    => $mapping['user_id'],
                        'bank_id'    => $mapping['bank_id'],
                        'created_at' => $currentTimestamp,
                        'updated_at' => $currentTimestamp,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('banks', function (Blueprint $table) {
            try {
                $table->dropUnique('banks_account_number_unique');
            } catch (\Throwable $e) {
            }

            try {
                $table->dropUnique('banks_slug_unique');
            } catch (\Throwable $e) {
            }

            if (!Schema::hasColumn('banks', 'user_id')) {
                $table->bigInteger('user_id')->unsigned()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
