<?php

namespace App\Services\Messaging\Contracts;

use Illuminate\Support\Str;

interface MessageActionHandler
{
    /**
     * Return action key handled by this handler (e.g. 'create_new_bill', 'payment', dst)
     */
    public function action(): string;

    /**
     * Build domain data (placeholder map) and return ['template_slug' => '...', 'replacements' => [...], 'to' => '628xxx', 'customer_id' => ?]
     */
    public function build(string $traceId): array;

    /**
     * Optional validation pre-dispatch (throw \RuntimeException untuk gagal cepat)
     */
    public function validate(string $traceId): void;
}
