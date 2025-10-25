<?php

namespace App\Http\Controllers\Mikrotik\Ppp;

use App\Models\Packages\Package;
use App\Models\Mikrotik;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Pppoe\ProfileResource;
use App\Services\Routerboard\Ppp\SecretService;
use Illuminate\Support\Str;
use App\Helpers\LogHelper;
use Exception;

class ProfileController extends Controller
{
    private SecretService $secretService;
    public function __construct(SecretService $secretService)
    {
        $this->secretService = $secretService;

        $this->middleware('permission:server-list|server-create|server-edit|server-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:server-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:server-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:server-delete', ['only' => ['delete']]);
        $this->middleware('permission:server-disable', ['only' => ['enable', 'disable']]);
    }

    public function index(Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();
        $allProfiles = $this->secretService->getPppProfile($mikrotik, $traceId);
        $Profiles = ProfileResource::collection($allProfiles);

        return view('mikrotiks.ppp.profiles.index', [
            'mikrotik' => $mikrotik,
            'profiles' => $Profiles,
        ]);
    }

    public function clone_profile(Request $request, Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();
        try {
            $mikrotikId = $mikrotik->id;
            $allProfiles = $this->secretService->getPppProfile($mikrotik, $traceId);
            $Profiles = ProfileResource::collection($allProfiles);

            $profileName = $Profiles->pluck('name')->toArray();
            $profileNames = collect($profileName);

            $packages = Package::where('mikrotik_id', $mikrotikId)->pluck('profile')->toArray();
            $packagesProfileNames = collect($packages);

            $profileNonPackage = $profileNames->diff($packagesProfileNames);
            $profileAddPackage = $Profiles->whereIn('name', $profileNonPackage)->toArray();

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotikId,
                'profile_count' => count($profileAddPackage),
            ], 200, 'Starting profile cloning process', $traceId);

            foreach ($profileAddPackage as $profile) {
                Package::create([
                    'mikrotik_id' => $mikrotikId,
                    'name' => $profile['name'],
                    'profile' => $profile['name'],
                    'slug' => str($profile['name'] . '-' . Str::random(4))->slug(),
                    'disabled' => 'false',
                    'description' => $profile['name'],
                    'price' => 0,
                ]);

                LogHelper::logInfo(null, [
                    'mikrotik_id' => $mikrotikId,
                    'profile' => $profile['name'],
                ], 200, 'Successfully added profile to Package', $traceId);
            }

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotikId,
                'total_added' => count($profileAddPackage),
            ], 200, 'Successfully cloned profiles to Package', $traceId);

            return redirect()->route('packets.index')
                ->with('success', 'Import profile to package successfully.');
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'error' => $e->getMessage(),
            ], 500, 'Failed to clone profiles', $traceId);

            return redirect()->back()->withErrors(['msg' => 'Failed to clone profiles: ' . $e->getMessage()]);
        }
    }
}
