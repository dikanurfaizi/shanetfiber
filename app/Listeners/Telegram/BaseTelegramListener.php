<?php

namespace App\Listeners\Telegram;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

abstract class BaseTelegramListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'telegram';
    public $tries = 3;
}
