<?php

namespace App\Http\Controllers\Subscription;

use App\Models\CustomerSubscription;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerSubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = CustomerSubscription::with(['customer', 'package', 'subscriptionDuration'])
            ->paginate(15);
        return view('customerSubscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        return view('customerSubscriptions.create');
    }

    public function store(Request $request)
    {
        // validasi data
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'required|exists:packages,id',
            'subscription_duration_id' => 'required|exists:subscription_durations,id',
            'status' => 'required|in:active,paused,cancelled,terminated',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'price_final' => 'required|numeric',
            'discount_type_final' => 'required|in:none,percent,amount',
            'discount_value_final' => 'required|numeric|min:0',
        ]);

        CustomerSubscription::create($validated);

        return redirect()->route('customerSubscriptions.index')
            ->with('success', 'Customer subscription created successfully.');
    }

    public function show(CustomerSubscription $customerSubscription)
    {
        return view('customerSubscriptions.show', compact('customerSubscription'));
    }

    public function edit(CustomerSubscription $customerSubscription)
    {
        return view('customerSubscriptions.edit', compact('customerSubscription'));
    }

    public function update(Request $request, CustomerSubscription $customerSubscription)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'required|exists:packages,id',
            'subscription_duration_id' => 'required|exists:subscription_durations,id',
            'status' => 'required|in:active,paused,cancelled,terminated',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'price_final' => 'required|numeric',
            'discount_type_final' => 'required|in:none,percent,amount',
            'discount_value_final' => 'required|numeric|min:0',
        ]);

        $customerSubscription->update($validated);

        return redirect()->route('customerSubscriptions.index')
            ->with('success', 'Customer subscription updated successfully.');
    }

    public function destroy(CustomerSubscription $customerSubscription)
    {
        $customerSubscription->delete();

        return redirect()->route('customerSubscriptions.index')
            ->with('success', 'Customer subscription deleted successfully.');
    }
}
