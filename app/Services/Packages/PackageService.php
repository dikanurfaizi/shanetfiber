<?php

namespace App\Services\Packages;

use App\Enums\SubscriptionDuration;
use App\Http\Requests\PackageStoreRequest;
use App\Http\Requests\PackageUpdateRequest;
use App\Models\Packages\DurationLabel;
use App\Models\Packages\Package;
use Illuminate\Support\Str;
use App\Services\Packages\PackageQueryService;
use Illuminate\Support\Facades\DB;
use App\Models\Packages\PackageDuration;

class PackageService
{
    use PackageLogTrait;

    protected PackageQueryService $queryService;

    public function __construct(PackageQueryService $queryService)
    {
        $this->queryService = $queryService;
    }

    public function store(PackageStoreRequest $request)
    {
        [$traceId, $startTime] = $this->startTrace();

        try {
            $package = DB::transaction(function () use ($request) {
                // 🔹 Generate slug
                $request->merge([
                    'slug' => str($request->name . '-' . Str::random(4))->slug(),
                ]);

                // 🔹 Siapkan data package
                $data = $request->except([
                    'price_formatted',
                    'installation_fee_formatted',
                    'duration_months'
                ]);

                $data['price'] = (int) str_replace('.', '', $request->price_formatted ?? 0);
                $data['installation_fee'] = (int) str_replace('.', '', $request->installation_fee_formatted ?? 0);

                // 🔹 Simpan package
                $package = Package::create($data);

                if (!$package || !$package->id) {
                    throw new \RuntimeException('Failed to create package');
                }

                // 🔹 Validasi input duration_months dengan Enum
                $durationMonths = (int) $request->duration_months;
                $enum = SubscriptionDuration::tryFrom($durationMonths);

                if (!$enum) {
                    throw new \InvalidArgumentException("Invalid duration value: {$durationMonths}");
                }

                // 🔹 Ambil / buat master duration_labels berdasarkan Enum
                $durationLabel = DurationLabel::firstOrCreate(
                    ['duration_months' => $enum->value],
                    [
                        'slug'    => str($enum->labelEn())->slug(),
                        'name_en' => $enum->labelEn(),
                        'name_id' => $enum->labelId(),
                    ]
                );

                // 🔹 Buat PackageDuration dengan duration_id (FK ke duration_labels.id)
                PackageDuration::create([
                    'package_id'     => $package->id,
                    'duration_id'    => $durationLabel->id, // Menggunakan Primary Key ID (1, 2, dst.)
                    'discount_type'  => $request->discount_type ?? 'none',
                    'discount_value' => $request->discount_value ?? 0,
                    'is_active'      => true,
                ]);

                return $package;
            });

            // ✅ Commit success
            $this->endTraceSuccess($request, $traceId, $startTime, [
                'package' => $package
            ], 'Successfully created new package');

            return $package;
        } catch (\Exception $e) {
            // ❌ Rollback otomatis oleh DB::transaction()
            $this->endTraceError($request, $traceId, $startTime, $e, 'Failed to create package');
            throw $e;
        }
    }

    public function update(PackageUpdateRequest $request, Package $package)
    {
        [$traceId, $startTime] = $this->startTrace();

        try {
            $data = $request->except(['price_formatted', 'installation_fee_formatted']);
            if (empty($data['name'])) unset($data['name']);

            $data['price'] = $request->filled('price_formatted')
                ? (int) str_replace('.', '', $request->price_formatted)
                : $package->price;

            $data['installation_fee'] = $request->filled('installation_fee_formatted')
                ? (int) str_replace('.', '', $request->installation_fee_formatted)
                : $package->installation_fee;

            $package->update($data);

            $this->endTraceSuccess($request, $traceId, $startTime, [
                'package_id' => $package->id,
                'changes' => $package->getChanges()
            ], 'Successfully updated package');

            return $package;
        } catch (\Exception $e) {
            $this->endTraceError($request, $traceId, $startTime, $e, 'Failed to update package');
            throw $e;
        }
    }

    public function enable($slug)
    {
        return $this->toggleStatus($slug, 'false', 'enable');
    }

    public function disable($slug)
    {
        return $this->toggleStatus($slug, 'true', 'disable');
    }

    protected function toggleStatus($slug, $state, $action)
    {
        [$traceId, $startTime] = $this->startTrace();

        try {
            $package = Package::where('slug', $slug)->firstOrFail();
            $package->disabled = $state;
            $package->save();

            $this->endTraceSuccess(null, $traceId, $startTime, [
                'package' => $package
            ], "Successfully {$action}d package");

            return $package;
        } catch (\Exception $e) {
            $this->endTraceError(null, $traceId, $startTime, $e, "Failed to {$action} package");
            throw $e;
        }
    }

    public function destroy($slug)
    {
        [$traceId, $startTime] = $this->startTrace();
        $package = Package::where('slug', $slug)->firstOrFail();
        $package->forceDelete();
        $this->endTraceSuccess(null, $traceId, $startTime, [
            'package' => $package
        ], 'Successfully deleted package');
    }

    public function toggleDisplay($slug, $value)
    {
        [$traceId, $startTime] = $this->startTrace();

        $package = Package::where('slug', $slug)->first();
        $package->display_to_customer = $value;
        $package->save();

        $this->endTraceSuccess(null, $traceId, $startTime, [
            'package_id' => $package->id,
            'display_to_customer' => $value
        ], 'Toggled display status');

        return $package;
    }

    public function show(Package $package)
    {
        [$traceId, $startTime] = $this->startTrace();

        try {
            $package = $this->queryService->loadData($package);

            $this->endTraceSuccess(null, $traceId, $startTime, [
                'package_id' => $package->id,
                'slug' => $package->slug,
                'category' => $package->category->name ?? null,
            ], 'Successfully retrieved package detail');

            return compact('package');
        } catch (\Exception $e) {
            $this->endTraceError(null, $traceId, $startTime, $e, 'Failed to load package detail');
            throw $e; // biar controller yang tangani redirect
        }
    }
}
