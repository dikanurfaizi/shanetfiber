<?php

namespace App\Helpers;

class ExecutionTimer
{
    private float $last;

    public function __construct()
    {
        $this->last = microtime(true);
    }

    public function lap(): float
    {
        $now = microtime(true);
        $exec = round($now - $this->last, 3);
        $this->last = $now;

        return $exec;
    }
}
