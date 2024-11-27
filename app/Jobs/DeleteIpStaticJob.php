<?php

namespace App\Jobs;

use App\Models\Mikrotik;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Services\Routerboard\IpStaticService;

class DeleteIpStaticJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Mikrotik $mikrotik;
    private string $simpleque_id;
    private string $arp_id;
    private string $ip_address;
    private string $address_list_name;
    private IpStaticService $IpStaticService;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $mikrotik, string $simpleque_id, string $arp_id, string $ip_address, string $address_list_name)
    {
        $this->mikrotik = $mikrotik;
        $this->simpleque_id = $simpleque_id;
        $this->arp_id = $arp_id;
        $this->ip_address = $ip_address;
        $this->address_list_name = $address_list_name;
        $this->IpStaticService = app(IpStaticService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->IpStaticService->deleteIpFromSimpleQueue($this->mikrotik, $this->simpleque_id);
        $this->IpStaticService->deleteIpFromArp($this->mikrotik, $this->arp_id);
        if ($this->address_list_name != 'none') {
            $address_list = $this->IpStaticService->getDataByIpAddressWithAddressListName($this->mikrotik, $this->ip_address, $this->address_list_name);
            if ($address_list != 'false') {
                $this->IpStaticService->deleteIpFromAddressList($this->mikrotik, $this->ip_address, $this->address_list_name);
            }
        }
    }
}
