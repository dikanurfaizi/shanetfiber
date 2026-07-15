<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\Whatsapp\WhatsappMessageMedia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use App\Helpers\LogHelper;
use Illuminate\Support\Str;

class WhatsappMediaController extends Controller
{
    public function show(WhatsappMessageMedia $media)
    {
        $traceId = (string) Str::uuid();

        // 1️⃣ Pastikan status downloaded
        if ($media->download_status !== 'downloaded') {
            LogHelper::logWarning(null, [
                'media_id' => $media->id,
                'download_status' => $media->download_status,
                'file_name' => $media->file_name
            ], 404, "[Whatsapp Media] Attempted to access media that is not fully downloaded", $traceId);

            abort(404);
        }

        // 2️⃣ Optional tapi penting: pastikan media memang milik conversation yang user boleh akses
        if (!$media->message || !$media->message->conversation) {
            LogHelper::logWarning(null, [
                'media_id' => $media->id,
                'message_id' => $media->message_id,
                'has_message' => isset($media->message),
                'has_conversation' => isset($media->message->conversation)
            ], 404, "[Whatsapp Media] Orphaned media or missing conversation relation", $traceId);

            abort(404);
        }

        // 3️⃣ Stream file (protected)
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($media->storage_disk);

        if (!$disk->exists($media->storage_path)) {
            // Log ini akan memberitahu Anda Laravel nyari ke mana
            LogHelper::logError(null, [
                'media_id' => $media->id,
                'disk' => $media->storage_disk,
                'path' => $media->storage_path
            ], 404, "[Whatsapp Media] File Hilang!", $traceId);

            LogHelper::logError(null, [
                'media_id' => $media->id,
                'full_path' => $disk->path($media->storage_path)
            ], 404, "[Whatsapp Media] Full Path yang dicari", $traceId);

            abort(404, "File tidak ditemukan di storage.");
        }

        // 4️⃣ Log Sukses (Audit Log Akses File)
        LogHelper::logInfo(null, [
            'media_id'        => $media->id,
            'message_id'      => $media->message_id,
            'conversation_id' => $media->message->conversation->id ?? null,
            'file_name'       => $media->file_name,
            'mime_type'       => $media->mime_type,
            'file_size'       => $media->file_size,
            'storage_disk'    => $media->storage_disk,
            'accessed_by'     => auth()->id() ?? 'guest' // Melacak siapa yang membuka file
        ], 200, "[Whatsapp Media] File streamed successfully", $traceId);

        return $disk->response(
            $media->storage_path,
            $media->file_name,
            ['Content-Type' => $media->mime_type]
        );
    }
}
