<?php

namespace Database\Seeders;

use App\Models\WhatsappGateway\WhatsappGateway;
use Illuminate\Database\Seeder;

class WhatsappGatewayTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        //=================Create Template Gateway============================
        WhatsappGateway::create([
            'slug' => 'gateway',
            'name' => 'WA Gateway',
            'disabled' => true,
            'remaining_day' => 0,
            'country_code' => 62,

        ]);
    }
}
