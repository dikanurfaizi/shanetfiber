<?php

namespace App\Jobs;

use App\Models\Mikrotik;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Services\Routerboard\Ppp\SecretService;

class EnablePppSecretJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private Mikrotik $mikrotik;
    private string $username;
    private SecretService $SecretService;
    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $mikrotik, string $username)
    {
        $this->mikrotik = $mikrotik;
        $this->username = $username;
        $this->SecretService = app(SecretService::class);
    }

    /**
     * Execute the job.
     */
    //  public function handle(): void
    // {
    //      $this->SecretService->enableSecret($this->mikrotik, $this->username);
    //  }

    public function handle()
    {
        $response = $this->SecretService->enableSecret($this->mikrotik, $this->username);
        return ($response);
    }

    public function getResponse()
    {
        $response = $this->handle();
        return ($response);
    }
}
