<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $oldMembers = DB::table('members')->get();

        foreach ($oldMembers as $m) {
            DB::table('memberships')->insert([
                'slug' => strtolower(str_replace(' ', '-', $m->type)),
                'name' => $m->type, // atau $m->type, sesuaikan kebutuhan
                'discount_type' => 'amount',
                'discount_value' => $m->discount,
                'is_active' => $m->status == 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('memberships')->truncate();
    }
};
