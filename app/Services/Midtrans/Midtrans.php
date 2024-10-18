<?php

namespace App\Services\Midtrans;

use Midtrans\Config;
use App\Models\Midtran;

class Midtrans
{
    protected $serverKey;
    protected $isProduction;
    protected $isSanitized;
    protected $is3ds;

    public function __construct()
    {

        // $this->serverKey = config('midtrans.server_key');
        //$this->isProduction = config('midtrans.is_production');
        $midtrans = Midtran::where('slug', 'midtrans')->first();

        if ($midtrans->midtrans_is_production == 'false') {
            $this->isProduction = false;
            $this->serverKey = $midtrans->midtrans_sandbox_server_key;
        } else {
            $this->isProduction = true;
            $this->serverKey = $midtrans->midtrans_server_key;
        }

        $this->isSanitized = config('midtrans.is_sanitized');
        $this->is3ds = config('midtrans.is_3ds');

        $this->_configureMidtrans();
    }

    public function _configureMidtrans()
    {
        Config::$serverKey = $this->serverKey;
        Config::$isProduction = $this->isProduction;
        Config::$isSanitized = $this->isSanitized;
        Config::$is3ds = $this->is3ds;
    }
}
