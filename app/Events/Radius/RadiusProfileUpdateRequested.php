<?php

namespace App\Events\Radius;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RadiusProfileUpdateRequested
{
    use Dispatchable, SerializesModels;

    /**
     * @param string|null $traceId ID tracing untuk logging (opsional)
     */
    public function __construct(
        public readonly string $traceId
    ) {}
}
