<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Paket\Paket;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingCycle extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function pakets()
    {
        return $this->hasMany(Paket::class);
    }
}
