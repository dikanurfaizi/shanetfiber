<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\WhatsApp\WhatsAppGateway;
use Illuminate\Http\Request;

class WhatsAppGatewayController extends Controller
{
    /**
     * INDEX — List all providers.
     */
    public function index()
    {
        $gateways = WhatsAppGateway::withCount('accounts')->get();

        return view('whatsapp_gateways.gateways.index', compact('gateways'));
    }

    /**
     * SHOW — Provider detail + all accounts.
     */
    public function show(WhatsAppGateway $whatsappgateway)
    {
        $whatsappgateway->load('accounts');

        return view('whatsapp_gateways.gateways.show', [
            'gateway' => $whatsappgateway,
        ]);
    }

    /**
     * EDIT — Provider-level edit page.
     */
    public function edit(WhatsAppGateway $whatsappgateway)
    {
        return view('whatsapp_gateways.gateways.edit', [
            'gateway' => $whatsappgateway,
        ]);
    }

    /**
     * UPDATE — Save provider info.
     */
    public function update(Request $request, WhatsAppGateway $whatsappgateway)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:120',
            'type'        => 'required|in:onprem,cloud',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $whatsappgateway->update([
            'name'        => $validated['name'],
            'type'        => $validated['type'],
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('whatsappgateway.show', $whatsappgateway)
            ->with('success', 'Gateway updated successfully');
    }
}
