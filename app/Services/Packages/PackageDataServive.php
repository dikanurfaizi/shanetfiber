<?php

namespace App\Services\Packages;

use App\Models\Packages\Package;

class PackageDataServive
{
    public function getById(array $packageIds): array
    {
        $packages = Package::whereIn('id', $packageIds)->get();
        $data = [];
        foreach ($packages as $package) {
            $data[$package->id] = [
                'name' => $package->name,
                'profile' => $package->profile,
                'slug' => $package->slug,
                'description' => $package->description,
                'disabled' => $package->disabled,
                'price' => $package->price,
                'is_installation_free' => $package->is_installation_free,
                'package_category_id' => $package->package_category_id,
            ];
        }
        return $data;
    }
}
