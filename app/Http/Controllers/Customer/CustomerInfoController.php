<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class CustomerInfoController extends Controller
{
    public function update(Request $request, Customer $customer)
    {
        // =========================
        // AUTHORIZATION
        // =========================
        $this->authorize('customer-edit', $customer);

        // =========================
        // VALIDATION
        // =========================
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'name'    => ['required', 'string', 'max:255'],
            'email'   => [
                'required',
                'email',
                Rule::unique('customers', 'email')->ignore($customer->id),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:30'],
        ]);

        // =========================
        // UPDATE CUSTOMER
        // =========================
        $customer->update($validated);

        // =========================
        // REDIRECT BACK TO VIEW MODE
        // =========================
        return redirect()
            ->route('customers.show', [
                $customer->slug,
                'tab' => 'info',
            ])
            ->with('success', 'Customer information updated successfully.');
    }
}
