<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;

return new class extends Migration {
    public function up(): void
    {
        $customers = DB::table('customers')
            ->whereNotNull('member_id')
            ->get();

        foreach ($customers as $c) {

            // --- Skip if member_id doesn't exist in old members table ---
            $oldMember = DB::table('members')->where('id', $c->member_id)->first();
            if (!$oldMember) continue;

            // --- Find mapped membership in new table ---
            $newMembership = DB::table('memberships')
                ->where('slug', strtolower(str_replace(' ', '-', $oldMember->type)))
                ->first();
            if (!$newMembership) continue;


            /*
            |--------------------------------------------------------------------------
            | Determine START DATE
            |--------------------------------------------------------------------------
            */
            if ($c->billing_option === '2' && !empty($c->billing_period)) {

                $startDate = $c->updated_at
                    ? Carbon::parse($c->updated_at)
                    : Carbon::parse($c->activation_date);

                $endDate = null;
            } elseif ($c->billing_option !== '2') {

                // contoh billing_period: "March 2025"
                $period = Carbon::createFromFormat('F Y', $c->billing_period);

                $startDate = $period->copy()->startOfMonth();
                $endDate   = $period->copy()->endOfMonth();
            } else {

                // fallback defensif (optional)
                $startDate = Carbon::parse($c->activation_date);
                $endDate   = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Insert into customer_memberships
            |--------------------------------------------------------------------------
            */
            DB::table('customer_memberships')->insert([
                'customer_id'    => $c->id,
                'membership_id'  => $newMembership->id,
                'start_date'     => $startDate,
                'end_date'       => $endDate,
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Drop old column member_id
        |--------------------------------------------------------------------------
        */
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'member_id')) {

                // 1) Drop foreign key
                try {
                    $table->dropForeign(['member_id']);
                } catch (\Throwable $e) {
                    // FK mungkin sudah tidak ada — ignore error
                }

                // 2) Drop the column
                try {
                    $table->dropColumn(['member_id', 'billing_option', 'billing_period']);
                } catch (\Throwable $e) {
                    // ignore if needed
                }
            }
        });
    }

    public function down(): void
    {
        // 1. Tambahkan kembali kolom member_id (nullable)
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('member_id')->nullable()->after('id');
            $table->integer('billing_option')->nullable()->after('member_id');
            $table->integer('billing_period')->nullable()->after('billing_option');
        });

        // 2. Isi kembali member_id dari customer_memberships yang masih aktif
        //    Hanya membership yang masih aktif kita restore sebagai member_id
        $records = DB::table('customer_memberships')
            ->where('is_active', true)
            ->get();

        foreach ($records as $cm) {
            DB::table('customers')
                ->where('id', $cm->customer_id)
                ->update([
                    'member_id' => $cm->membership_id
                ]);
        }

        // 3. Tambahkan kembali foreign key yang nullable
        Schema::table('customers', function (Blueprint $table) {
            // Tambahkan FK kembali — tidak masalah jika member_id null
            $table->foreign('member_id')
                ->references('id')
                ->on('members')
                ->nullOnDelete();
            // memakai nullOnDelete() = best practice untuk FK nullable
        });
    }
};
