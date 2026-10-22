<?php

namespace App\Console\Commands;

use App\Events\Radius\RadiusProfileUpdateRequested;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

class SyncPppProfilesToRadius extends Command
{
    protected $signature = 'radius:sync-profiles';
    protected $description = 'Pemicu asynchronous job sinkronisasi seluruh PPP Profiles ke FreeRADIUS';

    public function handle(): int
    {
        $traceId = (string) Str::uuid();
        $this->info('Memicu event sinkronisasi PPP Profiles ke FreeRADIUS...');

        try {
            event(new RadiusProfileUpdateRequested($traceId));

            $this->info("Event sinkronisasi PPP Profiles berhasil dipicu secara asynchronous (Trace ID: {$traceId}).");
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Gagal memicu event sinkronisasi profil ke FreeRADIUS: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
