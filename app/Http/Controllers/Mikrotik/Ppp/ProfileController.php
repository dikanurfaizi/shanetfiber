<?php

namespace App\Http\Controllers\Mikrotik\Ppp;

use App\Models\Paket;
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
        $allProfiles = $this->secretService->getPppProfile($mikrotik);
        $Profiles = ProfileResource::collection($allProfiles);

        return view('mikrotiks.ppp.profiles.index', [
            'mikrotik' => $mikrotik,
            'profiles' => $Profiles,
        ]);
    }

    public function clone_profile(Request $request, Mikrotik $mikrotik)
    {
        try {
            $mikrotikId = $mikrotik->id;
            $allProfiles = $this->secretService->getPppProfile($mikrotik);
            $Profiles = ProfileResource::collection($allProfiles);

            $profileName = $Profiles->pluck('name')->toArray();
            $profileNames = collect($profileName);

            $pakets = Paket::where('mikrotik_id', $mikrotikId)->pluck('profile')->toArray();
            $paketsProfileNames = collect($pakets);

            $profileNonPaket = $profileNames->diff($paketsProfileNames);
            $profileAddPaket = $Profiles->whereIn('name', $profileNonPaket)->toArray();

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotikId,
                'profile_count' => count($profileAddPaket),
            ], 200, 'Starting profile cloning process');

            foreach ($profileAddPaket as $profile) {
                Paket::create([
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
                ], 200, 'Successfully added profile to Paket');
            }

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotikId,
                'total_added' => count($profileAddPaket),
            ], 200, 'Successfully cloned profiles to Paket');

            return redirect()->route('packets.index')
                ->with('success', 'Import profile to paket successfully.');
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'error' => $e->getMessage(),
            ], 500, 'Failed to clone profiles');

            return redirect()->back()->withErrors(['msg' => 'Failed to clone profiles: ' . $e->getMessage()]);
        }
    }
}
