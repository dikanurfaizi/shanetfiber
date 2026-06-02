<?php

namespace App\Services\Messaging\Handlers;

use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use App\Helpers\LogHelper;

class PaymentChannelUpdateHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    /**
     * @param MessageBuilder $builder
     * @param string $recipient Nomor tujuan WhatsApp pelanggan
     * @param array $bankPayload Hasil gabungan data bank dan data customer dari Service
     * @param string $resolvedTemplate Slug template utama (e.g., 'info_perubahan_rekening_pembayaran')
     * @param string $category Kategori broadcast
     */
    public function __construct(
        private MessageBuilder $builder,
        private string $recipient,
        private array $bankPayload,
        private string $resolvedTemplate,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($recipient);
    }

    public function action(): string
    {
        return $this->resolvedTemplate;
    }

    public function validate(string $traceId): void
    {
        $this->validateBase($traceId);
    }

    public function build(string $traceId): array
    {
        // 👉 Definisikan replacement secara eksplisit agar klop dengan template database Anda
        $replacements = [
            '%customer_name%'   => $this->bankPayload['customer_name'] ?? '-',
            '%effective_date%'  => $this->bankPayload['effective_date'] ?? '-',
            '%services%'        => $this->bankPayload['services'] ?? 'Layanan Berlangganan',
            '%account_total%'   => $this->bankPayload['account_total'] ?? '0',
            '%account_list%'    => $this->bankPayload['account_list'] ?? '-', // Teks dinamis hasil gabungan looping bank & ewallet
            '%whatsapp_number%' => $this->bankPayload['whatsapp_number'] ?? '-',
        ];

        LogHelper::logInfo(
            null,
            [
                'trace_id' => $traceId,
                'to' => $this->recipient,
                'customer_name' => $replacements['%customer_name%']
            ],
            100,
            '[PaymentChannelUpdateHandler] Replacements structural mapped successfully'
        );

        return [
            'template_slug' => $this->resolvedTemplate,
            'to'            => $this->recipient,
            'customer_id'   => $this->bankPayload['customer_id'] ?? null,
            'replacements'  => $replacements, // Mengirim manifest data lengkap ke TemplateEngine
        ];
    }
}
