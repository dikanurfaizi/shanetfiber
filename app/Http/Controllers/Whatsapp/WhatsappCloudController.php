<?php

namespace App\Http\Controllers\WhatsApp;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\WhatsApp\Cloud\WhatsAppCloudTemplate;
use App\Models\WhatsApp\WhatsAppGateway;
use App\Models\WhatsApp\WhatsAppGatewayAccount;
use App\Services\WhatsApp\CloudAPI\Templates\Integration\MetaTemplateFetcherService;
use App\Services\WhatsApp\CloudAPI\Templates\Sync\TemplateSyncService;
use Illuminate\Support\Str;

class WhatsAppCloudController extends Controller
{
    public function index()
    {
        $templates = WhatsAppCloudTemplate::with('components')
            ->orderBy('name')
            ->paginate(20);

        return view('whatsapp.templates.index', compact('templates'));
    }

    public function sync(
        TemplateSyncService $syncService,
        MetaTemplateFetcherService $fetcher
    ) {
        $traceId = Str::uuid()->toString();

        $gateway = WhatsAppGateway::where('type', 'cloud')
            ->where('is_active', true)
            ->with(['accounts' => function ($q) {
                $q->where('is_active', true);
            }])
            ->first();

        if (!$gateway || $gateway->accounts->isEmpty()) {
            LogHelper::logError(null, [], 404, "Sync cancelled: No active account found", $traceId);
            return redirect()
                ->route('whatsapptemplate.index')
                ->with('error', 'No active WhatsApp gateway account found.');
        }

        $account = $gateway->accounts->first();

        // Log Awal Ringkas Produksi
        LogHelper::logInfo(null, [
            'triggered_by_user' => auth()->user()->name ?? 'Dian Ardiansyah',
            'account_id'        => $account->id,
            'meta_waba_id'      => $account->meta_waba_id,
        ], 200, "Initiate template sync", $traceId);

        try {
            $templates = $fetcher->fetchAllTemplates($account);

            if (empty($templates)) {
                LogHelper::logError(null, ['account_id' => $account->id], 400, "Sync aborted: Meta returned empty templates", $traceId);
                return redirect()
                    ->route('whatsapptemplate.index')
                    ->with('error', 'No templates returned from Meta API.');
            }

            $result = $syncService->syncFromMeta($account, $templates);

            // ===============================================================
            // 🔥 HANDLING JIKA TIDAK ADA PERUBAHAN DATA
            // ===============================================================
            $result = $syncService->syncFromMeta($account, $templates);

            // 1. Amankan pembacaan key dengan menyertakan default nilai 0 jika key tidak di-return oleh service
            $created = $result['created'] ?? 0;
            $updated = $result['updated'] ?? 0;
            $deleted = $result['deleted'] ?? 0;

            $totalChanges = $created + $updated + $deleted;

            // Handling jika data Meta dan Lokal sudah sama (tidak ada perubahan)
            if ($totalChanges === 0) {
                LogHelper::logInfo(null, [
                    'account_id'  => $account->id,
                    'total_meta'  => count($templates),
                ], 200, "Sync finished: All templates are already up-to-date", $traceId);

                return redirect()
                    ->route('whatsapptemplate.index')
                    ->with('info', 'Semua template sudah sesuai dengan Meta API (Tidak ada perubahan).');
            }

            // Jika ada perubahan (Gunakan variabel aman yang sudah di-declare di atas)
            LogHelper::logInfo(null, [
                'account_id' => $account->id,
                'created'    => $created,
                'updated'    => $updated,
                'deleted'    => $deleted,
            ], 200, "Sync completed with changes", $traceId);

            return redirect()
                ->route('whatsapptemplate.index')
                ->with('success', "Sinkronisasi berhasil. Ditambah: {$created}, Diperbarui: {$updated}, Dihapus: {$deleted}");
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'account_id' => $account->id,
                'exception'  => get_class($e),
                'message'    => $e->getMessage()
            ], 500, "Template sync thrown an exception", $traceId);

            return redirect()
                ->route('whatsapptemplate.index')
                ->with('error', 'Template sync failed: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $template = WhatsAppCloudTemplate::with('components.buttons')->findOrFail($id);

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
        $template = WhatsAppCloudTemplate::findOrFail($id);

        $gateway = WhatsAppGateway::where('is_active', true)->first();
        $account = WhatsAppGatewayAccount::where('whatsapp_gateway_id', $gateway->id)
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
