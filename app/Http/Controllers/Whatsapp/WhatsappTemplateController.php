<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\Whatsapp\WhatsappTemplate;
use App\Services\Whatsapp\TemplateSyncService;
use Illuminate\Support\Str;

class WhatsappTemplateController extends Controller
{
    public function index()
    {
        $templates = WhatsappTemplate::orderBy('name', 'ASC')->paginate(20);

        return view('whatsapp_gateways.templates.index', compact('templates'))->with('i', (request()->input('page', 1) - 1) * 10);
    }

    public function sync(TemplateSyncService $syncService)
    {
        $traceId = Str::uuid()->toString();
        $success = $syncService->syncAll($traceId);

        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'WhatsApp templates synchronized successfully.'
                : 'Failed to fetch templates from Meta Graph API.',
        ]);
    }
}
