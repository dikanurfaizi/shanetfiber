<?php

namespace App\Services\Auth;

use App\Models\Customers\Customer;
use App\Models\Packages\Package;
use App\Models\Websystem;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;
use App\Helpers\FormatPackage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class CustomerAuthService
{
    public function getLoginData()
    {
        // Deteksi Bahasa Aktif
        $locale = app()->getLocale(); // 'id' atau 'en'

        // 1. Optimasi Query Package (Tanpa Duplicate Join & Eager Loading Berlebihan)
        $packagesRaw = Package::select([
            'packages.id',
            'packages.name',
            'packages.speed_up_to',
            'packages.speed_unit',
            'packages.price',
            'packages.recommended_device_min',
            'packages.recommended_device_max',
            'packages.quota',
            'packages.include_modem',
            'packages.installation_fee',
            'packages.is_installation_free',
            'pc.name as category_name',
            'dl.name_en as duration_name_en',
            'dl.name_id as duration_name_id' // Pastikan ada kolom nama bahasa Indonesia di duration_labels
        ])
            ->join('package_categories as pc', 'packages.package_category_id', '=', 'pc.id')
            ->leftJoin('package_durations as pd', 'packages.id', '=', 'pd.package_id')
            ->leftJoin('duration_labels as dl', 'pd.duration_id', '=', 'dl.id')
            ->where('packages.display_to_customer', true)
            ->where('packages.price', '>', 0)
            ->get();

        $formatPackage = new FormatPackage();

        // 2. Map Data Hanya Dalam 1 Kali Loop & Dynamic Multi-Language Currency
        $packages = $packagesRaw->map(function ($package) use ($formatPackage, $locale) {
            $categoryName = $package->category_name ?? 'Uncategorized';
            $durationName = ($locale === 'en')
                ? ($package->duration_name_en ?? 'Monthly')
                : ($package->duration_name_id ?? 'Bulan');

            $formattedName = $formatPackage->formatPackageName($package->name);

            // Formatting Price berdasarkan Locale
            if ($locale === 'en') {
                // English: IDR 150,000/Monthly
                $formattedPrice = 'IDR ' . number_format($package->price, 0, '.', ',') . '/' . $durationName;
            } else {
                // Indonesia: Rp 150.000/Bulan
                $formattedPrice = 'Rp ' . number_format($package->price, 0, ',', '.') . '/' . $durationName;
            }

            return (object) [
                'category' => $categoryName,
                'package_name' => $package->name,
                'formatted_name' => $formattedName,
                'speed_up_to' => $package->speed_up_to,
                'speed_unit' => $package->speed_unit,
                'price' => $package->price,
                'formatted_price' => $formattedPrice,
                'duration_name' => $durationName,
                'recommended_device_min' => $package->recommended_device_min,
                'recommended_device_max' => $package->recommended_device_max,
                'quota' => $package->quota,
                'include_modem' => $package->include_modem,
                'installation_fee' => $package->installation_fee,
                'is_installation_free' => $package->is_installation_free,
                'wa_message' => urlencode("Halo, saya tertarik dengan paket {$formattedName}. Bisa dibantu informasinya?")
            ];
        });

        // 3. Cache Websystem Config
        $websystem = Cache::remember('websystem_config', 3600, function () {
            return Websystem::where('slug', 'websystem')->first();
        });

        $formatter = new FormatReceiver();
        $phoneNumber = $websystem ? $formatter->formatReceiver($websystem->phone, null, false) : '';
        $whatsappCall = $websystem ? $formatter->formatReceiver($websystem->phone, 'homepage', false) : '';
        $generalMessage = urlencode(__('customer.hero.general_message') ?? 'Halo, saya tertarik dengan package internet yang tersedia. Bisa dibantu informasinya?');

        // Ambil Unique Categories
        $categories = $packages->pluck('category')->unique()->values()->toArray();

        return compact('packages', 'websystem', 'phoneNumber', 'generalMessage', 'categories', 'whatsappCall');
    }

    public function attemptLogin(string $input, string $password)
    {
        LogHelper::logInfo(null, ['user_login' => $input], 200, "Attempting login");

        // Format Nomor HP (08xx -> 628xx atau 628xx -> 08xx)
        $localInput = null;
        if (str_starts_with($input, '0')) {
            $localInput = '62' . substr($input, 1);
        } elseif (str_starts_with($input, '62')) {
            $localInput = '0' . substr($input, 2);
        }

        // 4. Grouped Where Condition (Lebih Aman dan Bersih)
        $user = Customer::where(function ($query) use ($input, $localInput) {
            $query->where('email', $input)
                ->orWhere('contact', $input);

            if ($localInput) {
                $query->orWhere('contact', $localInput);
            }
        })->first();

        if (!$user) {
            LogHelper::logWarning(null, ['user' => $input], 404, "Login attempt failed. No user found.");
            return ['success' => false, 'message' => __('auth.failed') ?? 'Invalid credentials'];
        }

        if (!Hash::check($password, $user->password)) {
            LogHelper::logWarning(null, ['user' => $input], 400, "Password mismatch");
            return ['success' => false, 'message' => __('auth.failed') ?? 'Invalid credentials'];
        }

        auth()->guard('customer')->login($user);

        $userName = str_contains($user->name, '@')
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
