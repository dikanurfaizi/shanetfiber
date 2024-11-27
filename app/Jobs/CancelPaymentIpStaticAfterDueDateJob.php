<?php

namespace App\Jobs;

use App\Models\Mikrotik;
use App\Services\Routerboard\IpStaticService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Services\Routerboard\Ppp\SecretService;

class CancelPaymentIpStaticAfterDueDateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Mikrotik $mikrotik;
    private string $address_list_isolir;
    private string $ip_address;
    private string $comment;
    private string $simpleque_id;
    private IpStaticService $IpStaticService;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $mikrotik, string $address_list_isolir, string $ip_address, string $simpleque_id, string $comment)
    {
        $this->mikrotik = $mikrotik;
        $this->address_list_isolir = $address_list_isolir;
        $this->ip_address = $ip_address;
        $this->simpleque_id = $simpleque_id;
        $this->comment = $comment;
        $this->IpStaticService = app(IpStaticService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //  $this->IpStaticService->deleteIpFromAddressList($this->mikrotik, $this->ip_address, $this->address_list_isolir);
        $this->IpStaticService->addIpToAddressList($this->mikrotik, $this->ip_address, $this->address_list_isolir, $this->comment);
        $this->IpStaticService->setCommentSimpleque($this->mikrotik, $this->simpleque_id, $this->comment);
    }
}
