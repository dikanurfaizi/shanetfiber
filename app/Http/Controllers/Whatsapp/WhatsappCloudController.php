<?php

namespace App\Http\Controllers\Whatsapp;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Whatsapp\Cloud\WhatsappCloudTemplate;
use App\Models\Whatsapp\WhatsappGateway;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use App\Services\Whatsapp\CloudAPI\Templates\Integration\MetaTemplateFetcherService;
use App\Services\Whatsapp\CloudAPI\Templates\Sync\TemplateSyncService;
use Illuminate\Support\Str;

class WhatsappCloudController extends Controller
{
    public function index()
    {
        $templates = WhatsappCloudTemplate::with('components')
            ->orderBy('name')
            ->paginate(20);

        return view('whatsapp.templates.index', compact('templates'));
    }

    public function sync(
        TemplateSyncService $syncService,
        MetaTemplateFetcherService $fetcher
    ) {
        $traceId = Str::uuid()->toString();
        LogHelper::logInfo(null, [], 200, "Initiate sync", $traceId);

        $gateway = WhatsappGateway::where('is_active', true)->first();
        $account = WhatsappGatewayAccount::where('whatsapp_gateway_id', $gateway->id)
            ->where('is_active', true)->first();

        if (!$account) {
            return redirect()
                ->route('whatsapptemplate.index')
                ->with('error', 'No active WhatsApp gateway account found.');
        }

        try {
            $templates = $fetcher->fetchAllTemplates($account);

            if (empty($templates)) {
                return redirect()
                    ->route('whatsapptemplate.index')
                    ->with('error', 'No templates returned from Meta API.');
            }

            $result = $syncService->syncFromMeta($account, $templates);

            return redirect()
                ->route('whatsapptemplate.index')
                ->with('success', "Sync completed. Synced: {$result['synced']}, Failed: {$result['failed']}");
        } catch (\Throwable $e) {
            return redirect()
                ->route('whatsapptemplate.index')
                ->with('error', 'Template sync failed: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $template = WhatsappCloudTemplate::with('components.buttons')->findOrFail($id);

        $examples = [
            'header' => [],
            'body' => [],
        ];

        foreach ($template->components as $component) {

            if ($component->isHeader() && isset($component->example['header_text'][0])) {
                foreach ($component->example['header_text'] as $i => $val) {
                    $examples['header'][$i + 1] = $val;
                }
            }

            if ($component->isBody() && isset($component->example['body_text'][0])) {
                foreach ($component->example['body_text'][0] as $i => $val) {
                    $examples['body'][$i + 1] = $val;
                }
            }
        }

        return view('whatsapp.templates.show', compact('template', 'examples'));
    }

    public function syncSingle(
        $id,
        TemplateSyncService $syncService,
        MetaTemplateFetcherService $fetcher
    ) {
        $traceId = Str::uuid()->toString();
        // dd($id);
        $template = WhatsappCloudTemplate::findOrFail($id);

        $gateway = WhatsappGateway::where('is_active', true)->first();
        $account = WhatsappGatewayAccount::where('whatsapp_gateway_id', $gateway->id)
            ->where('is_active', true)
            ->first();

        if (!$account) {
            return back()->with('error', 'No active WhatsApp gateway account.');
        }

        try {
            // fetch single template from Meta
            $metaTemplate = $fetcher->fetchTemplateById(
                $account,
                $template->waba_template_id
            );

            if (!$metaTemplate) {
                return back()->with('error', 'Template not found in Meta.');
            }

            $syncService->syncFromMeta($account, [$metaTemplate]);

            return back()->with('success', "Template {$template->name} synced successfully.");
        } catch (\Throwable $e) {

            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }
}
