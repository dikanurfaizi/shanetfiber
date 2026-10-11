<?php

namespace App\Services\Messaging\Handlers;

use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use App\Models\WhatsApp\WhatsAppMessage;
use App\Models\Packages\Package;
use App\Helpers\LogHelper;

class UnknownCustomerHandler implements MessageActionHandler
{
    public function __construct(
        private string $sender,
        private WhatsAppMessage $whatsappMessage,
        private MessageBuilder $builder,
        private string $category,
    ) {}

    public function action(): string
    {
        return 'unknown_customer_handler';
    }

    public function validate(string $traceId): void
    {
        // Tidak ada validasi khusus
    }

    public function build(string $traceId): array
    {
        if (!$this->whatsappMessage) {
            return [
                'template_slug' => $this->whatsappMessage->slug,
                'to'            => $this->sender,
                'replacements'  => [
                    '%wrong%' => "Terjadi kesalahan. Mohon coba lagi.",
                ],
            ];
        }

        // Replacement default: TIDAK ADA (kosong)
        $replacements = [];

        // -----------------------------------------------------
        // 2. Jika slug = unknown_info_paket → tambahkan daftar paket
        // -----------------------------------------------------
        if ($this->whatsappMessage->slug === 'unknown_info_paket') {
            $packages = Package::select(
                'packages.name as package_name',
                'packages.price',
                'package_categories.name as category_name',
                'duration_labels.name_id as duration_name'
            )
                ->join('package_categories', 'packages.package_category_id', '=', 'package_categories.id')
                ->join('package_durations', 'packages.id', '=', 'package_durations.package_id')
                ->join('duration_labels', 'package_durations.duration_id', '=', 'duration_labels.id')
                ->where('packages.display_to_customer', 1)
                ->where('packages.disabled', 'false')
                ->where('packages.price', '>', 0)
                ->where('package_categories.is_active', 1)
                ->where('package_durations.is_active', 1)
                ->get();

            $list = "";
            $items = [];

            foreach ($packages as $p) {
                $price = $this->builder->currency($p->price);
                $items[] = "- {$p->category_name} {$p->package_name} - {$price} {$p->duration_name}";
            }

            $list = implode("\n", $items);
            $replacements['%packages%'] = $list;
        }

        // -----------------------------------------------------
        // 3. Return nilai sesuai format standar semua handler
        // -----------------------------------------------------
        return [
            'template_slug' => $this->whatsappMessage->slug,
            'to'            => $this->sender,
            'replacements'  => $replacements,
        ];
    }
}
