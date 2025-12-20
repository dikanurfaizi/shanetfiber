<?php

namespace App\Http\Controllers\Package;

use App\Models\Packages\PackageDuration;
use App\Models\Packages\Package;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\LogHelper;
use Illuminate\Support\Str;

class PackageDurationController extends Controller
{
    public function index()
    {
        $durations = PackageDuration::with('package')->paginate(15);
        return view('subscriptionDurations.index', compact('durations'));
    }

    public function create()
    {
        $packages = Package::where('disabled', 'false')->firstOrFail();
        return view('subscriptionDurations.create', compact('packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'name_en' => 'required|string|max:255',
            'name_id' => 'required|string|max:255',
            'duration_months' => 'required|integer|min:1',
            'discount_type' => 'required|in:none,percent,amount',
            'discount_value' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        PackageDuration::create($validated);

        return redirect()->route('subscriptionDurations.index')
            ->with('success', 'Subscription duration created successfully.');
    }

    public function show(PackageDuration $subscriptionDuration)
    {
        return view('subscriptionDurations.show', compact('subscriptionDuration'));
    }

    public function edit($id)
    {
        $subscriptionDurations = PackageDuration::find($id);
        return view('subscriptionDurations.edit', compact('subscriptionDurations', 'package'));
    }

    public function update(Request $request, PackageDuration $subscriptionDuration)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'name_en' => 'required|string|max:255',
            'name_id' => 'required|string|max:255',
            'duration_months' => 'required|integer|min:1',
            'discount_type' => 'required|in:none,percent,amount',
            'discount_value' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $subscriptionDuration->update($validated);

        return redirect()->route('subscriptionDurations.index')
            ->with('success', 'Subscription duration updated successfully.');
    }

    public function destroy(PackageDuration $subscriptionDuration)
    {
        $subscriptionDuration->delete();

        return redirect()->route('subscriptionDurations.index')
            ->with('success', 'Subscription duration deleted successfully.');
    }

    public function manage($slug)
    {
        $traceId = (string) Str::uuid();
        $startTime = microtime(true); // ⏱️ Start timing

        try {
            // 🔹 Ambil package berdasarkan slug
            $package = Package::where('slug', $slug)->firstOrFail();

            // 🔹 Ambil dan hitung subscription durations
            $subscriptionDurations = $package->subscriptionDurations()->get()->map(function ($duration) use ($package) {
                $basePrice = $package->price;
                $finalPrice = $basePrice;

                switch ($duration->discount_type) {
                    case 'percent':
                        $finalPrice = $basePrice - ($basePrice * ($duration->discount_value / 100));
                        break;
                    case 'amount':
                        $finalPrice = $basePrice - $duration->discount_value;
                        break;
                    default:
                        // none → tidak ada diskon
                        break;
                }

                $duration->final_price = max($finalPrice, 0); // Pastikan tidak negatif
                return $duration;
            });

            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2); // ms

            LogHelper::logInfo(null, [
                'package_slug' => $slug,
                'durations_count' => $subscriptionDurations->count(),
            ], 200, 'Successfully managed subscription durations', $traceId, $duration);

            return view('subscriptionDurations.manage', [
                'package' => $package,
                'subscriptionDurations' => $subscriptionDurations,
            ])->with('i', (request()->input('page', 1) - 1) * 20);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);

            LogHelper::logError(null, [
                'error_message' => "Package not found: $slug",
            ], 404, 'Package not found', $traceId, $duration);

            return redirect()->route('packages.index')
                ->withErrors(['msg' => "Package with slug '$slug' not found."]);
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);

            LogHelper::logError(null, [
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed to manage subscription durations', $traceId, $duration);

            return redirect()->route('packages.index')
                ->withErrors(['msg' => 'Failed to manage subscription durations. Please try again.']);
        }
    }
}
