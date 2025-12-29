<?php

namespace App\Http\Controllers\Customer;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Services\Customers\Billing\UpdateService;
use DomainException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerBillingController extends Controller
{
    public function update(
        Request $request,
        Customer $customer,
        UpdateService $updateService
    ) {
        $startTime = microtime(true);
        $traceId = (string) Str::uuid();
        $this->authorize('billing-edit', $customer);
        try {

            $validated = $request->validate([
                'package_id'           => ['required', 'exists:packages,id'],
                'package_duration_id'  => ['required', 'exists:package_durations,id'],
                'member_id'            => ['nullable', 'exists:memberships,id'],
                'billing_option'       => ['required', 'in:monthly,one_time'],
                'billing_periode_name' => ['nullable', 'string'],

                'addons'               => ['nullable', 'array'],
                'addons.*.addon_id'    => ['nullable', 'exists:addons,id'],
                'addons.*.qty'         => [
                    'nullable',
                    'integer',
                    'min:1',
                    'exclude_without:addons.*.addon_id',
                ],
                'addons.*.unit_price'  => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'exclude_without:addons.*.addon_id',
                ],
            ]);

            LogHelper::logInfo(null, ['payload_addon' => $validated['addons'] ?? []], 200, 'ADDONS_PAYLOAD', $traceId);

            $addons = collect($validated['addons'] ?? [])
                ->filter(
                    fn($a) =>
                    !empty($a['addon_id']) &&
                        isset($a['qty']) && (int) $a['qty'] > 0 &&
                        isset($a['unit_price'])
                )
                ->map(fn($a) => [
                    'addon_id'   => (int) $a['addon_id'],
                    'qty'        => (int) $a['qty'],
                    'unit_price' => (float) $a['unit_price'],
                ])
                ->values();

            $validated['addons'] = $addons;


            $updateService->updateAndRegenerate(
                $customer,
                $validated,
                $traceId,
                $startTime
            );

            return redirect()
                ->route('customers.show', [$customer->slug, 'tab' => 'billing'])
                ->with('success', 'Billing configuration updated and regenerated successfully.');
        } catch (DomainException $e) {
            LogHelper::logError($e, [
                'customer_id' => $customer->id,
            ], 422, 'Failed to update customer billing', $traceId);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update billing configuration: ' . $e->getMessage());
        } catch (Exception $e) {
            LogHelper::logError($e, [
                'customer_id' => $customer->id,
            ], 500, 'Failed to update customer billing', $traceId);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update billing configuration: ' . $e->getMessage());
        }
    }
}
