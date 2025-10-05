<?php

namespace App\Models\Network;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OltUpdateLog extends Model
{
    use HasFactory;

    protected $table = 'olt_update_logs';
    public $timestamps = false;

    protected $fillable = [
        'updated_at',
        'success_count',
        'failed_count',
        'details',
        'olt_id',
        'ont_count',
        'status',
        'duration',
        'username'
    ];

    // Relasi: UpdateLog belongsTo OLT
    public function olt()
    {
        return $this->belongsTo(Olt::class, 'olt_id');
    }
}
