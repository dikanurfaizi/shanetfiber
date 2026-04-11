<?php

namespace App\Jobs\Whatsapp;

use App\Helpers\LogHelper;
use App\Models\Whatsapp\WhatsappGateway;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use App\Models\Whatsapp\WhatsappMessageMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class DownloadWhatsappMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // retry tiap 60 detik

    public function __construct(
        public int $mediaId,
        public string $traceId
    ) {}

    public function handle(): void
    {
        $media = WhatsappMessageMedia::find($this->mediaId);

        if (!$media) {
            return;
        }

        // Hanya skip jika sudah sukses
        if ($media->download_status === 'downloaded') {
            return;
        }

        try {

            $account = $this->resolveActiveAccount();

            $accessToken = decrypt($account->meta_user_access_token);

            $graphUrl = rtrim($account->meta_graph_url, '/');
            $version  = trim($account->meta_graph_version, '/');

            // 1️⃣ Ambil metadata media dari Graph API
            $metaResponse = Http::withToken($accessToken)
                ->timeout(15)
                ->get("{$graphUrl}/{$version}/{$media->wa_media_id}");

            if (!$metaResponse->successful()) {
                throw new RuntimeException(
                    'Failed fetching media metadata: ' . $metaResponse->body()
                );
            }

            $mediaUrl = $metaResponse->json('url');

            if (!$mediaUrl) {
                throw new RuntimeException('Media URL not returned by Graph API');
            }

            // 2️⃣ Download file binary
            $fileResponse = Http::withToken($accessToken)
                ->timeout(30)
                ->get($mediaUrl);

            if (!$fileResponse->successful()) {
                throw new RuntimeException(
                    'Failed downloading media file: ' . $fileResponse->body()
                );
            }

            $fileContent = $fileResponse->body();

            // Optional: batasi ukuran (misal 20MB)
            if (strlen($fileContent) > 20 * 1024 * 1024) {
                throw new RuntimeException('Media file exceeds 20MB limit');
            }

            // 3️⃣ Generate safe storage path
            $extension = $this->resolveExtension($media->mime_type);

            // nama original (jika ada)
            $generatedName = $media->wa_media_id . '.' . $extension;

            $storagePath = now()->format('Y/m/') . $generatedName;

            // Storage::disk($media->storage_disk)->put($storagePath, $fileContent);
            Storage::disk('whatsapp_private')->put($storagePath, $fileContent);

            $fileSize = strlen($fileContent);

            // 4️⃣ Update status sukses
            $media->update([
                'file_name'         => empty($media->file_name) ? $generatedName : $media->file_name,
                'file_size'         => $fileSize,
                'storage_disk'      => 'whatsapp_private',
                'storage_path'      => $storagePath,
                'download_status'   => 'downloaded',
                'download_error'    => null,
                'downloaded_at'     => now(),
            ]);
        } catch (Throwable $e) {

            LogHelper::logError(
                null,
                ['media_id' => $this->mediaId, 'error' => $e->getMessage()],
                500,
                'Failed to download WhatsApp media',
                $this->traceId
            );

            // Update jadi failed (tapi retry tetap bisa jalan)
            $media->update([
                'download_status' => 'failed',
                'download_error'  => $e->getMessage(),
            ]);

            throw $e; // biar queue retry
        }
    }

    private function resolveActiveAccount(): WhatsappGatewayAccount
    {
        $gateway = WhatsappGateway::where('is_active', true)
            ->where('type', 'cloud')
            ->first();

        if (!$gateway) {
            throw new RuntimeException('No active WhatsApp gateway found');
        }

        $account = WhatsappGatewayAccount::where('whatsapp_gateway_id', $gateway->id)
            ->where('is_active', true)
            ->first();

        if (!$account) {
            throw new RuntimeException('No active WhatsApp gateway account found');
        }

        if (!$account->meta_user_access_token) {
            throw new RuntimeException('Meta access token not configured');
        }

        return $account;
    }

    private function resolveExtension(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'application/pdf' => 'pdf',
            'video/mp4' => 'mp4',
            default => 'bin',
        };
    }
}
