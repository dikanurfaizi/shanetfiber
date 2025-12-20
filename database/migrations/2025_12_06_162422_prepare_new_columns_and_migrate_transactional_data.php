<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('billings', function (Blueprint $table) {

            // Tambah kolom baru dulu, TANPA drop apa pun
            if (!Schema::hasColumn('billings', 'paid_by_user_id')) {
                $table->unsignedBigInteger('paid_by_user_id')->nullable()->after('teller_name');
            }

            if (!Schema::hasColumn('billings', 'status_new')) {
                $table->enum('status_new', ['BL', 'PL', 'LS', 'PR', 'VD'])
                    ->default('BL')
                    ->after('status');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | MIGRASI STATUS LAMA → BARU
        | MIGRASI TELLER NAME → PAID_BY_USER_ID
        |--------------------------------------------------------------------------
        */
        $billings = DB::table('billings')->get();

        foreach ($billings as $b) {
            $newStatus = match ($b->status) {
                'BL' => 'BL',
                'PL' => 'PL',
                'LS' => 'LS',
                'NP' => 'VD',   // NOT IN PERIOD => VOID
                default => 'BL'
            };

            DB::table('billings')
                ->where('id', $b->id)
                ->update(['status_new' => $newStatus]);

            $paidUserId = null;

            // 1. Attempt direct match (mostly fails because name changed)
            if ($b->teller_name) {
                $user = DB::table('users')->where('name', $b->teller_name)->first();
                if ($user) {
                    $paidUserId = $user->id;
                }
            }

            // 2. Handle known historical renames
            if (!$paidUserId && $b->teller_name == 'Admin ShaNet Fiber') {
                $user = DB::table('users')->where('name', 'Inar Hapsah')->first();
                if ($user) {
                    $paidUserId = $user->id;
                }
            }

            // 3. FINAL: if still null → keep null (NOT assign 1)
            DB::table('billings')->where('id', $b->id)->update([
                'paid_by_user_id' => $paidUserId
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn(['paid_by_user_id', 'status_new']);
        });
    }
};
