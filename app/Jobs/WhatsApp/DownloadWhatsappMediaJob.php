<?php

namespace App\Jobs\WhatsApp;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\WhatsApp\WhatsAppGateway;
use App\Models\WhatsApp\WhatsAppGatewayAccount;
use App\Models\WhatsApp\WhatsAppMessageMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DownloadWhatsAppMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // Retry setiap 60 detik

    public function __construct(
        public int $mediaId,
        public string $traceId
    ) {}

    public function handle(): void
    {
        $timer = new ExecutionTimer();

        // 1️⃣ Structural Initiation Log
        LogHelper::logInfo(
            null,
            [
                'media_id'    => $this->mediaId,
                'attempt'     => $this->attempts(),
                'max_tries'   => $this->tries,
            ],
            200,
            '[DownloadWhatsAppMediaJob] Job execution initiated',
            $this->traceId,
            $timer->lap()
        );

        $media = WhatsAppMessageMedia::find($this->mediaId);

        if (!$media) {
            LogHelper::logWarning(
                null,
                ['media_id' => $this->mediaId],
                404,
                '[DownloadWhatsAppMediaJob] Skipped: Media record not found',
                $this->traceId,
                $timer->lap()
            );
            return;
        }

        // Hanya skip jika sudah sukses
        if ($media->download_status === 'downloaded') {
            LogHelper::logInfo(
                null,
                [
                    'media_id'     => $this->mediaId,
                    'wa_media_id'  => $media->wa_media_id,
                    'storage_path' => $media->storage_path,
                ],
                200,
                '[DownloadWhatsAppMediaJob] Skipped: Media already downloaded',
                $this->traceId,
                $timer->lap()
            );
            return;
        }

        try {
            // 2️⃣ Resolve Gateway Account & Credentials
            $account = $this->resolveActiveAccount();
            $accessToken = decrypt($account->meta_user_access_token);

            $graphUrl = rtrim($account->meta_graph_url, '/');
            $version  = trim($account->meta_graph_version, '/');

            // 3️⃣ Ambil metadata media dari Graph API
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

            LogHelper::logInfo(
                null,
                [
                    'media_id'    => $this->mediaId,
                    'wa_media_id' => $media->wa_media_id,
                    'mime_type'   => $media->mime_type,
                ],
                200,
                '[DownloadWhatsAppMediaJob] Graph API metadata resolved successfully',
                $this->traceId,
                $timer->lap()
            );

            // 4️⃣ Download file binary
            $fileResponse = Http::withToken($accessToken)
                ->timeout(30)
                ->get($mediaUrl);

            if (!$fileResponse->successful()) {
                throw new RuntimeException(
                    'Failed downloading media file: ' . $fileResponse->body()
                );
            }

            $fileContent = $fileResponse->body();
            $fileSize    = strlen($fileContent);

            // Guard Clause: Batasi ukuran (20MB)
            if ($fileSize > 20 * 1024 * 1024) {
                throw new RuntimeException('Media file exceeds 20MB limit');
            }

            // 5️⃣ Generate Safe Storage Path & Save
            $extension     = $this->resolveExtension($media->mime_type);
            $generatedName = $media->wa_media_id . '.' . $extension;

            $subFolder   = now()->format('Y/m/');
            $storagePath = $subFolder . $generatedName;

            $diskRoot           = config('filesystems.disks.whatsapp_private.root');
            $absoluteFolderPath = rtrim($diskRoot, '/') . '/' . rtrim($subFolder, '/');

            $targetUser  = 'www';
            $targetGroup = 'www';

            // Simpan file ke Disk 'whatsapp_private'
            Storage::disk('whatsapp_private')->put($storagePath, $fileContent);

            // 6️⃣ Force Permission via OS Code (Jika dikirim dari Worker Root)
            $absoluteFilePath = rtrim($diskRoot, '/') . '/' . $storagePath;

            if (file_exists($absoluteFilePath)) {
                chown($absoluteFilePath, $targetUser);
                chgrp($absoluteFilePath, $targetGroup);
                chmod($absoluteFilePath, 0664);

                if (is_dir($absoluteFolderPath)) {
                    chown($absoluteFolderPath, $targetUser);
                    chgrp($absoluteFolderPath, $targetGroup);
                    chmod($absoluteFolderPath, 0775);

                    $absoluteYearPath = dirname($absoluteFolderPath);
                    if (is_dir($absoluteYearPath)) {
                        chown($absoluteYearPath, $targetUser);
                        chgrp($absoluteYearPath, $targetGroup);
                        chmod($absoluteYearPath, 0775);
                    }
                }
            }

            // 7️⃣ Update Status DB Ke Sukses
            $media->update([
                'file_name'       => empty($media->file_name) ? $generatedName : $media->file_name,
                'file_size'       => $fileSize,
                'storage_disk'    => 'whatsapp_private',
                'storage_path'    => $storagePath,
                'download_status' => 'downloaded',
                'download_error'  => null,
                'downloaded_at'   => now(),
            ]);

            // 8️⃣ Success Completion Log
            LogHelper::logInfo(
                null,
                [
                    'media_id'     => $this->mediaId,
                    'wa_media_id'  => $media->wa_media_id,
                    'file_name'    => $generatedName,
                    'file_size'    => $fileSize,
                    'storage_path' => $storagePath,
                ],
                200,
                '[DownloadWhatsAppMediaJob] Media downloaded and stored successfully',
                $this->traceId,
                $timer->lap()
            );
        } catch (Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(
                null,
                [
                    'media_id'        => $this->mediaId,
                    'attempt'         => $this->attempts(),
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                '[DownloadWhatsAppMediaJob] Failed executing WhatsApp media download',
                $this->traceId,
                $timer->lap()
            );

            // Update status DB ke failed (agar dapat terdeteksi untuk perbaikan/retry)
            if (isset($media)) {
                $media->update([
                    'download_status' => 'failed',
                    'download_error'  => $e->getMessage(),
                ]);
            }

            throw $e; // Lempar exception agar mekanisme Queue Retry bawaan Laravel tetap berjalan
        }
    }

    private function resolveActiveAccount(): WhatsAppGatewayAccount
    {
        $gateway = WhatsAppGateway::where('is_active', true)
            ->where('type', 'cloud')
            ->first();

        if (!$gateway) {
            throw new RuntimeException('No active WhatsApp gateway found');
        }

        $account = WhatsAppGatewayAccount::where('whatsapp_gateway_id', $gateway->id)
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
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'application/pdf' => 'pdf',
            'video/mp4'       => 'mp4',
            default           => 'bin',
        };
    }
}
