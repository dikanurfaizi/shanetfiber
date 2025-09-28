<?php

namespace App\Models\Network;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Onu extends Model
{
    use HasFactory;

    protected $table = 'onus';

    protected $fillable = [
        'olt_id',
        'onu_index',
        'name',
        'tx_power',
        'rx_power',
        'status',
        'last_updated',
        'mac_address',
        'temperature',
    ];

    public $timestamps = true;

    public function olt()
    {
        return $this->belongsTo(Olt::class, 'olt_id');
    }
}
