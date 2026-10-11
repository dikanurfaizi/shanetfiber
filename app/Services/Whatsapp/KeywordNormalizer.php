<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Str;

class KeywordNormalizer
{
    public function normalize(string $text): string
    {
        $t = Str::lower(trim($text));

        // Hapus semua simbol, emoji, tanda baca
        $t = preg_replace('/[^a-z0-9 ]+/i', '', $t);

        // Sederhanakan spasi
        $t = preg_replace('/\s+/', ' ', $t);

        return trim($t);
    }

    public function isStop(string $text): bool
    {
        $n = $this->normalize($text);

        // Kasus 1 — persis "stop"
        if ($n === 'stop') return true;

        // Kasus 2 — stop di depan ("stop ya" / "stop dong")
        if (Str::startsWith($n, 'stop ')) return true;

        // Kasus 3 — kata pertama stop ("stop me", "stop all")
        if (explode(' ', $n)[0] === 'stop') return true;

        return false;
    }
}
