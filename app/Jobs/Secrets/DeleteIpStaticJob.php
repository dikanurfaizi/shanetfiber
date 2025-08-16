<?php

namespace App\Jobs\Secrets;

use App\Models\Mikrotik;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Routerboard\IpStaticService;

class DeleteIpStaticJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Mikrotik $mikrotik;
    private string $simpleque_id;
    private string $arp_id;
    private string $ip_address;
    private string $address_list_name;
    private string $traceId;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $mikrotik, string $simpleque_id, string $arp_id, string $ip_address, string $address_list_name, string $traceId)
    {
        $this->mikrotik = $mikrotik;
        $this->simpleque_id = $simpleque_id;
        $this->arp_id = $arp_id;
        $this->ip_address = $ip_address;
        $this->address_list_name = $address_list_name;
        $this->traceId = $traceId;
    }

    /**
     * Execute the job.
     */
    public function handle(IpStaticService $IpStaticService): void
    {
        $IpStaticService->deleteIpFromSimpleQueue($this->mikrotik, $this->simpleque_id, $this->traceId);
        $IpStaticService->deleteIpFromArp($this->mikrotik, $this->arp_id, $this->traceId);
        if ($this->address_list_name != 'none') {
            $address_list = $IpStaticService->getDataByIpAddressWithAddressListName($this->mikrotik, $this->ip_address, $this->address_list_name, $this->traceId);
            if ($address_list != 'false') {
                $IpStaticService->deleteIpFromAddressList($this->mikrotik, $this->ip_address, $this->address_list_name, $this->traceId);
            }
        }
    }
}
