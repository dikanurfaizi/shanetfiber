<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\Whatsapp\WhatsappGateway;
use Illuminate\Http\Request;

class WhatsappGatewayController extends Controller
{
    /**
     * INDEX — List all providers.
     */
    public function index()
    {
        $gateways = WhatsappGateway::with('accounts')->get();

        return view('whatsapp_gateways.gateways.index', compact('gateways'));
    }

    /**
     * SHOW — Provider detail + all accounts.
     */
    public function show(WhatsappGateway $whatsappgateway)
    {
        // dd($whatsappgateway);
        $whatsappgateway->load('accounts');

        return view('whatsapp_gateways.gateways.show', [
            'gateway' => $whatsappgateway,
        ]);
    }

    /**
     * EDIT — Provider-level edit page.
     * Provider hanya punya:
     * - name
     * - type (onprem/cloud)
     * - description
     * - is_active
     */
    public function edit(WhatsappGateway $whatsappgateway)
    {
        return view('whatsapp_gateways.gateways.edit', [
            'gateway' => $whatsappgateway,
        ]);
    }

    /**
     * UPDATE — Save provider info.
     */
    public function update(Request $request, WhatsappGateway $whatsappgateway)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'type' => 'required|in:onprem,cloud',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Jika provider diaktifkan, nonaktifkan provider lain
        if ($request->boolean('is_active')) {
            WhatsappGateway::where('id', '!=', $whatsappgateway->id)
                ->update(['is_active' => false]);
        }

        $whatsappgateway->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('whatsappgateway.show', $whatsappgateway)
            ->with('success', 'Gateway updated successfully');
    }
}
