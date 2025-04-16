<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bank;
use App\Models\User;

class FillUserIdInBanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Bank::chunk(100, function ($banks) {
            foreach ($banks as $bank) {
                $user = User::where('name', $bank->name)->first();
                $bank->user_id = $user ? $user->id : 1;  // fallback ke id 1
                $bank->save();
            }
        });
    }
}
