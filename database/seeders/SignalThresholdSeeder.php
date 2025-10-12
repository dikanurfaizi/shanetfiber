<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Network\SignalThreshold;

class SignalThresholdSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'signal-too-strong',
                'label' => 'Signal Too Strong',
                'min_value' => -15,
                'max_value' => -1,
                'description' => 'To strong (> -15 dBm)',
            ],
            [
                'name' => 'signal-good',
                'label' => 'Good Signal',
                'min_value' => -24,
                'max_value' => -15,
                'description' => 'Good (-24 up to -15 dBm)',
            ],
            [
                'name' => 'signal-low',
                'label' => 'Weaken Signal',
                'min_value' => -25,
                'max_value' => -24,
                'description' => 'Weaken (-25 up to -24 dBm)',
            ],
            [
                'name' => 'signal-too-low',
                'label' => 'Poor Signal',
                'min_value' => -30,
                'max_value' => -25,
                'description' => 'Poor (< -25 dBm)',
            ],
        ];

        foreach ($data as $item) {
            SignalThreshold::updateOrCreate(['name' => $item['name']], $item);
        }
    }
}
