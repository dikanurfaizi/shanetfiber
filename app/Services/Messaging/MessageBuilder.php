<?php
// app/Services/Messaging/MessageBuilder.php
namespace App\Services\Messaging;

use App\Helpers\FormatReceiver;
use Carbon\Carbon;

class MessageBuilder
{
    public function __construct(private FormatReceiver $formatter = new FormatReceiver()) {}

    public function phone(?string $raw, ?string $check = null): ?string
    {
        return $this->formatter->formatReceiver($raw, $check);
    }

    public function currency(int|float|null $n): string
    {
        return 'Rp. ' . number_format($n ?? 0, 2, ',', '.');
    }

    public function dateId(?string $date, string $fmt = 'd F Y'): ?string
    {
        if (!$date) return null;
        return Carbon::parse($date)->locale('id')->translatedFormat($fmt);
    }
}
