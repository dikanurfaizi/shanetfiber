<?php

namespace App\Services\Packages;

use App\Models\Packages\Package;
use App\Models\Packages\PackageCategories;
use App\Models\Packages\PppProfile;

class PackageQueryService
{
    public function getIndexData()
    {
        return Package::orderBy('name', 'ASC')->paginate(20);
    }

    public function getCreateFormData()
    {
        $profiles = PppProfile::where('disabled', 'false')
            ->orderBy('profile_name')
            ->get(['id', 'profile_name']);

        $categories = PackageCategories::where('is_active', true)->get();

        return compact('profiles', 'categories');
    }

    public function getEditData($slug)
    {
        $profiles = PppProfile::where('disabled', 'false')
            ->orderBy('profile_name')
            ->get(['id', 'profile_name']);

        $categories = PackageCategories::where('is_active', true)->get();

        $package = Package::with('subscriptionDurations')->where('slug', $slug)->firstOrFail();

        return compact('package', 'profiles', 'categories');
    }

    public function getDetailBySlug(string $slug)
    {
        return Package::with(['category', 'subscriptionDurations'])
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
