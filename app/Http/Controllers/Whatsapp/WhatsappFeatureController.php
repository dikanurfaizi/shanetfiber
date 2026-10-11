<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\WhatsApp\WhatsAppFeature;
use App\Models\WhatsApp\WhatsAppGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhatsAppFeatureController extends Controller
{
    public function index(Request $request)
    {
        $query = WhatsAppFeature::query();

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

        WhatsAppFeature::create(array_merge($validated, [
            'slug' => Str::uuid(),
            'alias' => Str::lower(Str::slug($validated['name'], '_')),
            'created_by' => auth()->user()->name,
            'updated_by' => auth()->user()->name,
        ]));

        return redirect()
            ->route('whatsappfeature.index')
            ->with('success', __('whatsapp.feature.message.created'));
    }

    public function show(WhatsAppFeature $whatsappfeature)
    {
        // dd($whatsappfeature);
        return view('whatsapp.features.show', compact('whatsappfeature'));
    }

    public function edit(WhatsAppFeature $whatsappfeature)
    {
        return view('whatsapp.features.edit', compact('whatsappfeature'));
    }

    public function update(Request $request, WhatsAppFeature $whatsappfeature)
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

    public function delete(WhatsAppFeature $whatsappfeature)
    {
        return view('whatsapp.features.delete', compact('whatsappfeature'));
    }

    public function destroy(WhatsAppFeature $whatsappfeature)
    {
        $whatsappfeature->delete();

        return redirect()
            ->route('whatsappfeature.index')
            ->with('success', __('whatsapp.feature.message.deleted'));
    }

    public function activate(WhatsAppFeature $whatsappfeature)
    {
        $whatsappfeature->update(['is_active' => true]);
        return redirect()->route('whatsappfeature.index')->with('success', 'WhatsApp Feature activated successfully.');
    }

    public function deactivate(WhatsAppFeature $whatsappfeature)
    {
        $whatsappfeature->update(['is_active' => false]);
        return redirect()->route('whatsappfeature.index')->with('success', 'WhatsApp Feature deactivated successfully.');
    }

    public function schedule(WhatsAppFeature $whatsappfeature, Request $request)
    {
        $validate = $request->validate([
            'schedule' => 'required|array',
        ]);

        $whatsappfeature->update(['schedule' => $validate['schedule']]);
        return redirect()->route('whatsappfeature.index')->with('success', 'WhatsApp Feature schedule updated successfully.');
    }

    public function unschedule(WhatsAppFeature $whatsappfeature)
    {
        $whatsappfeature->update(['schedule' => null]);
        return redirect()->route('whatsappfeature.index')->with('success', 'WhatsApp Feature unscheduled successfully.');
    }

    public function toggleSchedule(WhatsAppFeature $whatsappfeature)
    {
        $whatsappfeature->update(['is_scheduled' => !$whatsappfeature->is_scheduled]);
        return redirect()->route('whatsappfeature.index')->with('success', 'WhatsApp Feature schedule toggled successfully.');
    }
}
