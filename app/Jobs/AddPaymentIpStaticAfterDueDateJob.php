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

class AddPaymentIpStaticAfterDueDateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Mikrotik $mikrotik;
    private string $ip_address;
    private string $comment;
    private string $comment_address_list;
    private string $simpleque_id;
    private string $addresst_list_name;
    private string $traceId;

    /**
     * Create a new job instance.
     */
    public function __construct(Mikrotik $mikrotik, string $ip_address, string $simpleque_id, string $addresst_list_name, string $comment, string $comment_address_list, string $traceId)
    {
        $this->mikrotik = $mikrotik;
        $this->ip_address = $ip_address;
        $this->simpleque_id = $simpleque_id;
        $this->comment = $comment;
        $this->addresst_list_name = $addresst_list_name;
        $this->comment_address_list = $comment_address_list;
        $this->traceId = $traceId;
    }

    /**
     * Execute the job.
     */
    public function handle(IpStaticService $IpStaticService): void
    {
        //$comment_from_cm = 'Create from Customer Management';
        $IpStaticService->deleteIpFromAddressList($this->mikrotik, $this->ip_address, $this->addresst_list_name, $this->traceId);
        $IpStaticService->setCommentSimpleque($this->mikrotik, $this->simpleque_id, $this->comment, $this->traceId);
    }
}
