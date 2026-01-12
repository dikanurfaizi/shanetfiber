<?php

namespace App\Services\Packages;

use App\Enums\SubscriptionDuration as SubsDurationEnum;
use App\Models\Packages\Package;
use Illuminate\Support\Str;
use App\Services\Packages\PackageQueryService;
use Illuminate\Support\Facades\DB;
use App\Models\Packages\PackageDuration;

class PackageService
{
    use PackageLogTrait;

    protected $query;

    public function __construct(PackageQueryService $query)
    {
        $this->query = $query;
    }

    public function store($request)
    {
        [$traceId, $startTime] = $this->startTrace();

        try {
            $package = DB::transaction(function () use ($request, $traceId, $startTime) {
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

                // 🔹 Buat subscription duration
                $durationMonths = (int) $request->duration_months;
                $enum = SubsDurationEnum::tryFrom($durationMonths);

                if (!$enum) {
                    throw new \InvalidArgumentException("Invalid duration value: {$durationMonths}");
                }

                PackageDuration::create([
                    'package_id' => $package->id,
                    'duration_months' => $durationMonths,
                    'name_en' => $enum->labelEn(),
                    'name_id' => $enum->labelId(),
                ]);

                return $package;
            });

            // ✅ Commit success
            $this->endTraceSuccess($request, $traceId, $startTime, [
                'package' => $package
            ], 'Successfully created new package');

            return $package;
        } catch (\Exception $e) {
            // ❌ Rollback otomatis dilakukan oleh DB::transaction()
            $this->endTraceError($request, $traceId, $startTime, $e, 'Failed to create package');
            throw $e;
        }
    }

    public function update($request, Package $package)
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
            $package = $this->query->loadData($package);

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
