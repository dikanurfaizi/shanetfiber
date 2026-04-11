<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\Whatsapp\WhatsappMessageMedia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use App\Helpers\LogHelper;

class WhatsappMediaController extends Controller
{
    public function show(WhatsappMessageMedia $media)
    {
        // 1️⃣ Pastikan status downloaded
        if ($media->download_status !== 'downloaded') {
            abort(404);
        }

        // 2️⃣ Optional tapi penting: pastikan media memang milik conversation yang user boleh akses
        if (!$media->message || !$media->message->conversation) {
            abort(404);
        }

        // 3️⃣ Stream file (protected)
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($media->storage_disk);

        if (!$disk->exists($media->storage_path)) {
            // Log ini akan memberitahu Anda Laravel nyari ke mana
            LogHelper::logError(null, ['disk' => $media->storage_disk, 'path' => $media->storage_path], 404, "File Hilang!");
            LogHelper::logError(null, ['full_path' => $disk->path($media->storage_path)], 404, "Full Path yang dicari ");
            abort(404, "File tidak ditemukan di storage.");
        }

        return $disk->response(
            $media->storage_path,
            $media->file_name,
            ['Content-Type' => $media->mime_type]
        );
    }
}
