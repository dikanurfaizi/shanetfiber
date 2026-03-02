<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\Whatsapp\WhatsappMessageMedia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

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

        return $disk->response(
            $media->storage_path,
            $media->file_name,
            ['Content-Type' => $media->mime_type]
        );
    }
}
