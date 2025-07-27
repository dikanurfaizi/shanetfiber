<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingCycle extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function pakets()
    {
        return $this->hasMany(\App\Models\Paket::class);
    }
}
