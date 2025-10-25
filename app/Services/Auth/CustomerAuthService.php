<?php

namespace App\Services\Auth;

use App\Models\Customer;
use App\Models\Packages\Package;
use App\Models\Websystem;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;
use App\Helpers\FormatPackage;
use Illuminate\Support\Facades\Hash;

class CustomerAuthService
{
    public function getLoginData()
    {
        $packages = Package::select([
            'packages.*',
            'pc.name as category_name',
            'sd.name_en as duration_name'
        ])
            ->leftJoin('subscription_durations as sd', 'packages.id', '=', 'sd.package_id')
            ->join('package_categories as pc', 'packages.package_category_id', '=', 'pc.id')
            ->where('packages.display_to_customer', true)
            ->where('packages.price', '>', 0)
            ->with(['category', 'subscriptionDurations'])
            ->get()
            ->map(function ($package) {
                return (object) [
                    'category' => $package->category_name ?? $package->category->name ?? 'Uncategorized',
                    'package_name' => $package->name,
                    'speed_up_to' => $package->speed_up_to,
                    'speed_unit' => $package->speed_unit,
                    'price' => $package->price,
                    'name_en' => $package->duration_name ?? $package->subscriptionDurations->first()->name_en ?? 'Monthly',
                    'recommended_device_min' => $package->recommended_device_min,
                    'recommended_device_max' => $package->recommended_device_max,
                    'quota' => $package->quota,
                    'include_modem' => $package->include_modem,
                    'installation_fee' => $package->installation_fee,
                    'is_installation_free' => $package->is_installation_free
                ];
            });

        $websystem = Websystem::where('slug', 'websystem')->first();
        $formatter = new FormatReceiver();

        $phoneNumber = $formatter->formatReceiver($websystem->phone, null, false);
        $whatsappCall = $formatter->formatReceiver($websystem->phone, 'homepage', false);
        $generalMessage = urlencode('Halo, saya tertarik dengan package internet yang tersedia. Bisa dibantu informasinya?');

        $formatPackage = new FormatPackage();
        $packages = $packages->map(function ($package) use ($formatPackage) {
            $package->formatted_name = $formatPackage->formatPackageName($package->package_name);
            $package->wa_message = urlencode("Halo, saya tertarik dengan paket {$package->formatted_name}. Bisa dibantu informasinya?");
            $package->formatted_price = 'Rp ' . number_format($package->price, 0, ',', '.') . '/' . $package->name_en;
            return $package;
        });

        $categories = $packages->pluck('category')->unique()->toArray();

        return compact('packages', 'websystem', 'phoneNumber', 'generalMessage', 'categories', 'whatsappCall');
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
