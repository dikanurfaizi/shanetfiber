<?php

namespace App\Http\Controllers\Subscription;

use App\Models\SubscriptionDuration;
use App\Models\Paket\Paket;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SubscriptionDurationController extends Controller
{
    public function index()
    {
        $durations = SubscriptionDuration::with('paket')->paginate(15);
        return view('subscriptionDurations.index', compact('durations'));
    }

    public function create()
    {
        $pakets = Paket::all();
        return view('subscriptionDurations.create', compact('pakets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'paket_id' => 'required|exists:pakets,id',
            'name_en' => 'required|string|max:255',
            'name_id' => 'required|string|max:255',
            'duration_months' => 'required|integer|min:1',
            'discount_type' => 'required|in:none,percent,amount',
            'discount_value' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        SubscriptionDuration::create($validated);

        return redirect()->route('subscriptionDurations.index')
            ->with('success', 'Subscription duration created successfully.');
    }

    public function show(SubscriptionDuration $subscriptionDuration)
    {
        return view('subscriptionDurations.show', compact('subscriptionDuration'));
    }

    public function edit(SubscriptionDuration $subscriptionDuration)
    {
        $pakets = Paket::all();
        return view('subscriptionDurations.edit', compact('subscriptionDuration', 'pakets'));
    }

    public function update(Request $request, SubscriptionDuration $subscriptionDuration)
    {
        $validated = $request->validate([
            'paket_id' => 'required|exists:pakets,id',
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

    public function destroy(SubscriptionDuration $subscriptionDuration)
    {
        $subscriptionDuration->delete();

        return redirect()->route('subscriptionDurations.index')
            ->with('success', 'Subscription duration deleted successfully.');
    }
}
