<?php

namespace Database\Seeders;

use App\Models\TelegramGateway\TelegramGateway;
use Illuminate\Database\Seeder;

class TelegramGatewayTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TelegramGateway::create([
            'slug' => 'gateway',
            'name' => 'ShaNetTestFlightBot',
            'disabled' => false,
            'description' => 'Telegram Official Shanum Network Fiber',
            'token' => '7771584514:AAETvxuFn_vL54wE1R3q1s8ZQ-E3XhF-dWA',
        ]);
    }
}
