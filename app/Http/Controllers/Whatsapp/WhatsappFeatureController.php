<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\Whatsapp\WhatsappFeature;
use App\Models\Whatsapp\WhatsappGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhatsappFeatureController extends Controller
{
    public function index(Request $request)
    {
        $query = WhatsappFeature::query();

        if ($request->has('q') && trim($request->q) !== '') {
            $query->where('name', 'like', '%' . trim($request->q) . '%');
        }

        $features = $query->orderBy('name')->paginate(10);

        return view('whatsapp.features.index', compact('features'));
    }

    public function create()
    {
        return view('whatsapp.features.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:onprem,cloud',
            'is_active' => 'boolean',
            'is_scheduled' => 'boolean',
            'schedule' => 'nullable|array',
        ]);

        // Guard: scheduler hanya boleh di cloud
        if ($validated['type'] !== 'cloud') {
            $validated['is_scheduled'] = false;
            $validated['schedule'] = null;
        }

        WhatsappFeature::create(array_merge($validated, [
            'slug' => Str::uuid(),
            'alias' => Str::lower(Str::slug($validated['name'], '_')),
            'created_by' => auth()->user()->name,
            'updated_by' => auth()->user()->name,
        ]));

        return redirect()
            ->route('whatsappfeature.index')
            ->with('success', __('whatsapp.feature.message.created'));
    }

    public function show(WhatsappFeature $whatsappfeature)
    {
        // dd($whatsappfeature);
        return view('whatsapp.features.show', compact('whatsappfeature'));
    }

    public function edit(WhatsappFeature $whatsappfeature)
    {
        return view('whatsapp.features.edit', compact('whatsappfeature'));
    }

    public function update(Request $request, WhatsappFeature $whatsappfeature)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:onprem,cloud',
            'is_active' => 'boolean',
            'is_scheduled' => 'boolean',
            'schedule' => 'nullable|array',
            'schedule.timezone' => 'required_if:is_scheduled,1',
            'schedule.days' => 'required_if:is_scheduled,1|array|min:1',
            'schedule.time_window.start' => 'required_if:is_scheduled,1',
            'schedule.time_window.end' => 'required_if:is_scheduled,1',
        ]);

        // Guard 1: feature mati → scheduler mati
        if (!$validated['is_active']) {
            $validated['is_scheduled'] = false;
        }

        // Guard 2: schedule hanya diizinkan untuk type cloud
        if ($validated['type'] !== 'cloud') {
            $validated['is_scheduled'] = false;
            $validated['schedule'] = null;
        }

        $whatsappfeature->update(array_merge($validated, [
            'updated_by' => auth()->user()->name,
        ]));

        return redirect()
            ->route('whatsappfeature.show', $whatsappfeature->slug)
            ->with('success', __('whatsapp.feature.message.updated'));
    }

    public function delete(WhatsappFeature $whatsappfeature)
    {
        return view('whatsapp.features.delete', compact('whatsappfeature'));
    }

    public function destroy(WhatsappFeature $whatsappfeature)
    {
        $whatsappfeature->delete();

        return redirect()
            ->route('whatsappfeature.index')
            ->with('success', __('whatsapp.feature.message.deleted'));
    }

    public function activate(WhatsappFeature $whatsappfeature)
    {
        $whatsappfeature->update(['is_active' => true]);
        return redirect()->route('whatsappfeature.index')->with('success', 'WhatsApp Feature activated successfully.');
    }

    public function deactivate(WhatsappFeature $whatsappfeature)
    {
        $whatsappfeature->update(['is_active' => false]);
        return redirect()->route('whatsappfeature.index')->with('success', 'WhatsApp Feature deactivated successfully.');
    }

    public function schedule(WhatsappFeature $whatsappfeature, Request $request)
    {
        $validate = $request->validate([
            'schedule' => 'required|array',
        ]);

        $whatsappfeature->update(['schedule' => $validate['schedule']]);
        return redirect()->route('whatsappfeature.index')->with('success', 'WhatsApp Feature schedule updated successfully.');
    }

    public function unschedule(WhatsappFeature $whatsappfeature)
    {
        $whatsappfeature->update(['schedule' => null]);
        return redirect()->route('whatsappfeature.index')->with('success', 'WhatsApp Feature unscheduled successfully.');
    }

    public function toggleSchedule(WhatsappFeature $whatsappfeature)
    {
        $whatsappfeature->update(['is_scheduled' => !$whatsappfeature->is_scheduled]);
        return redirect()->route('whatsappfeature.index')->with('success', 'WhatsApp Feature schedule toggled successfully.');
    }
}
