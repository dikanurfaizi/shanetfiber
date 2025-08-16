<?php

namespace App\Services\Auth;

use App\Models\Customer;
use App\Models\Paket\Paket;
use App\Models\Websystem;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;
use App\Helpers\FormatPackage;
use Illuminate\Support\Facades\Hash;

class CustomerAuthService
{
    public function getLoginData()
    {
        $pakets = Paket::select([
            'pakets.*',
            'pc.name as category_name',
            'sd.name_en as duration_name'
        ])
            ->leftJoin('subscription_durations as sd', 'pakets.id', '=', 'sd.paket_id')
            ->join('paket_categories as pc', 'pakets.paket_category_id', '=', 'pc.id')
            ->where('pakets.display_to_customer', true)
            ->where('pakets.price', '>', 0)
            ->with(['category', 'subscriptionDurations'])
            ->get()
            ->map(function ($paket) {
                return (object) [
                    'category' => $paket->category_name ?? $paket->category->name ?? 'Uncategorized',
                    'package_name' => $paket->name,
                    'speed_up_to' => $paket->speed_up_to,
                    'speed_unit' => $paket->speed_unit,
                    'price' => $paket->price,
                    'name_en' => $paket->duration_name ?? $paket->subscriptionDurations->first()->name_en ?? 'Monthly',
                    'recommended_device_min' => $paket->recommended_device_min,
                    'recommended_device_max' => $paket->recommended_device_max,
                    'quota' => $paket->quota,
                    'include_modem' => $paket->include_modem,
                    'installation_fee' => $paket->installation_fee,
                    'is_installation_free' => $paket->is_installation_free
                ];
            });

        $websystem = Websystem::where('slug', 'websystem')->first();
        $formatter = new FormatReceiver();

        $phoneNumber = $formatter->formatReceiver($websystem->phone, null, false);
        $whatsappCall = $formatter->formatReceiver($websystem->phone, 'homepage', false);
        $generalMessage = urlencode('Halo, saya tertarik dengan paket internet yang tersedia. Bisa dibantu informasinya?');

        $formatPackage = new FormatPackage();
        $pakets = $pakets->map(function ($paket) use ($formatPackage) {
            $paket->formatted_name = $formatPackage->formatPackageName($paket->package_name);
            $paket->wa_message = urlencode("Halo, saya tertarik dengan paket {$paket->formatted_name}. Bisa dibantu informasinya?");
            $paket->formatted_price = 'Rp ' . number_format($paket->price, 0, ',', '.') . '/' . $paket->name_en;
            return $paket;
        });

        $categories = $pakets->pluck('category')->unique()->toArray();

        return compact('pakets', 'websystem', 'phoneNumber', 'generalMessage', 'categories', 'whatsappCall');
    }

    public function attemptLogin(string $input, string $password)
    {
        LogHelper::logInfo(null, ['user_login' => $input], 200, "Attempting login");

        // handle local vs international format
        $localInput = null;
        if (substr($input, 0, 1) === '0') {
            $localInput = '62' . substr($input, 1);
        } elseif (substr($input, 0, 2) === '62') {
            $localInput = '0' . substr($input, 2);
        }

        $user = Customer::where('email', $input)
            ->orWhere('contact', $input)
            ->orWhere('contact', $localInput)
            ->first();

        if (!$user) {
            LogHelper::logWarning(null, ['user' => $input], 404, "Login attempt failed. No user found.");
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        LogHelper::logInfo(null, ['user' => $user->id], 200, "User found. Checking password...");

        if (!Hash::check($password, $user->password)) {
            LogHelper::logWarning(null, ['user' => $input], 400, "Password mismatch");
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        auth()->guard('customer')->login($user);

        $userName = strpos($user->name, '@') !== false
            ? explode('@', $user->name)[1]
            : $user->name;

        LogHelper::logInfo(null, ['user' => $userName], 200, "Successfully Login");

        return ['success' => true, 'user' => $user];
    }

    public function logout()
    {
        auth()->guard('customer')->logout();
    }
}
