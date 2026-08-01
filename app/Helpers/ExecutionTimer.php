<?php

namespace App\Helpers;

class ExecutionTimer
{
    private float $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    public static function start(): self
    {
        return new self();
    }

    /**
     * Mengembalikan durasi waktu sejak timer dibuat (dalam milidetik/ms)
     */
    public function lap(): int
    {
        return (int) round((microtime(true) - $this->startTime) * 1000);
    }
}
