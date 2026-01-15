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

    public function discountCurrency($amount): string
    {
        return $this->currency(abs($amount ?? 0));
    }

    public function addonsSection($addons): string
    {
        if ($addons->isEmpty()) return '';

        $lines = $addons->map(
            fn($item) =>
            sprintf(
                "• %s (%d x %s) = %s",
                $item->name,
                $item->qty,
                $this->currency($item->unit_price),
                $this->currency($item->subtotal)
            )
        )->implode("\n");

        return "\n\n*Addons*:\n" . $lines;
    }

    public function memberDiscountSection(?string $member, ?float $discount): string
    {
        $lines = [];

        if ($discount && $discount > 0) {
            $lines[] = "• Potongan Harga: " . $this->currency(abs($discount));
        }

        if ($member && strtolower($member) !== 'non member') {
            $lines[] = "• Kategori: {$member}";
        }

        if (empty($lines)) return '';

        return "\n\n*Member dan Diskon*:\n" . implode("\n", $lines);
    }
}
